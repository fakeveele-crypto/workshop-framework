<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;

class CustomerController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View
    {
        $this->ensureKantinTables();

        return view('kantin.customer.index', [
            'guestId' => $this->previewGuestId(),
            'vendors' => Vendor::query()->orderBy('nama_vendor')->get(),
        ]);
    }

    public function menusByVendor(Request $request, int $idvendor): JsonResponse
    {
        $this->ensureKantinTables();

        if (! Vendor::query()->whereKey($idvendor)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor tidak ditemukan.',
            ], 404);
        }

        $menus = Menu::query()
            ->where('idvendor', $idvendor)
            ->orderBy('nama_menu')
            ->get(['idmenu', 'nama_menu', 'harga', 'path_gambar']);

        return response()->json([
            'success' => true,
            'data' => $menus->map(function (Menu $menu) {
                return [
                    'idmenu' => $menu->idmenu,
                    'nama_menu' => $menu->nama_menu,
                    'harga' => (int) $menu->harga,
                    'path_gambar' => $menu->path_gambar,
                ];
            })->values(),
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $this->ensureKantinTables();

        $validated = $request->validate([
            'guest_id' => ['required', 'string', 'max:50'],
            'idvendor' => ['required', 'integer', 'exists:vendor,idvendor'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.idmenu' => ['required', 'integer', 'exists:menu,idmenu'],
            'items.*.nama_menu' => ['required', 'string', 'max:255'],
            'items.*.harga' => ['required', 'integer', 'min:0'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'items.*.subtotal' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                $vendorId = (int) $validated['idvendor'];
                $requestedItems = collect($validated['items']);

                $menus = Menu::query()
                    ->where('idvendor', $vendorId)
                    ->whereIn('idmenu', $requestedItems->pluck('idmenu')->all())
                    ->get()
                    ->keyBy('idmenu');

                if ($menus->count() !== $requestedItems->count()) {
                    throw new RuntimeException('Ada menu yang tidak sesuai dengan vendor terpilih.');
                }

                $calculatedItems = $requestedItems->map(function (array $item) use ($menus) {
                    $menu = $menus->get((int) $item['idmenu']);

                    if (! $menu) {
                        throw new RuntimeException('Menu tidak ditemukan.');
                    }

                    $harga = (int) $menu->harga;
                    $jumlah = (int) $item['jumlah'];
                    $subtotal = $harga * $jumlah;

                    return [
                        'idmenu' => (int) $menu->idmenu,
                        'nama_menu' => (string) $menu->nama_menu,
                        'harga' => $harga,
                        'jumlah' => $jumlah,
                        'subtotal' => $subtotal,
                    ];
                })->values();

                $calculatedTotal = (int) $calculatedItems->sum('subtotal');

                if ($calculatedTotal !== (int) $validated['total']) {
                    throw new RuntimeException('Total belanja tidak valid.');
                }

                $this->syncUsersSequence();

                $guestUser = User::query()->create([
                    'name' => 'Guest',
                    'email' => 'guest_'.Str::uuid().'@guest.local',
                    'password' => Str::random(40),
                    'role' => 'guest',
                ]);

                $guestName = $this->formatGuestName((int) $guestUser->id);
                $guestUser->forceFill(['name' => $guestName])->save();

                $orderPayload = [
                    'nama' => $guestName,
                    'timestamp' => now(),
                    'total' => $calculatedTotal,
                    'metode_bayar' => null,
                    'status_bayar' => 0,
                ];

                if (Schema::hasColumn('pesanan', 'iduser')) {
                    $orderPayload['iduser'] = (int) $guestUser->id;
                }

                if (Schema::hasColumn('pesanan', 'external_id')) {
                    $orderPayload['external_id'] = null;
                }

                if (Schema::hasColumn('pesanan', 'snap_token')) {
                    $orderPayload['snap_token'] = null;
                }

                $order = Pesanan::query()->create($orderPayload);

                $externalId = (string) $order->idpesanan;
                if (Schema::hasColumn('pesanan', 'external_id')) {
                    $order->update(['external_id' => $externalId]);
                }

                foreach ($calculatedItems as $item) {
                    $order->detail_pesanan()->create([
                        'idmenu' => $item['idmenu'],
                        'jumlah' => $item['jumlah'],
                        'harga' => $item['harga'],
                        'subtotal' => $item['subtotal'],
                        'timestamp' => now(),
                        'catatan' => null,
                    ]);
                }

                $invoiceUrl = $this->createInvoiceUrl($order);

                if (Schema::hasColumn('pesanan', 'snap_token')) {
                    $order->update(['snap_token' => $invoiceUrl]);
                }

                return [
                    'order_id' => $order->idpesanan,
                    'external_id' => $externalId,
                    'guest_id' => $guestName,
                    'snap_token' => $invoiceUrl,
                    'invoice_url' => $invoiceUrl,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat. Anda akan diarahkan ke halaman pembayaran Xendit.',
                'data' => $result,
            ]);
        } catch (Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Gagal memproses checkout.',
            ], 422);
        }
    }

    public function xenditCallback(Request $request): JsonResponse
    {
        $this->ensureKantinTables();

        $payload = $request->all();
        $callbackToken = (string) config('services.xendit.callback_token');
        $incomingToken = (string) $request->header('x-callback-token', '');

        if ($callbackToken !== '' && ! hash_equals($callbackToken, $incomingToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Callback token Xendit tidak valid.',
            ], 403);
        }

        $externalId = (string) ($payload['external_id'] ?? '');

        if ($externalId === '') {
            return response()->json([
                'success' => false,
                'message' => 'external_id tidak ditemukan di payload callback.',
            ], 422);
        }

        $order = Schema::hasColumn('pesanan', 'external_id')
            ? Pesanan::query()->where('external_id', $externalId)->first()
            : Pesanan::query()->find((int) $externalId);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan.',
            ], 404);
        }

        $invoiceStatus = strtoupper((string) ($payload['status'] ?? ''));
        if (in_array($invoiceStatus, ['PAID', 'SETTLED'], true)) {
            $updateData = [
                'status_bayar' => 1,
                'metode_bayar' => 1,
            ];

            if (Schema::hasColumn('pesanan', 'snap_token')) {
                $updateData['snap_token'] = (string) ($payload['invoice_url'] ?? $order->snap_token);
            }

            $order->update($updateData);
        }

        return response()->json(['success' => true]);
    }

    private function createInvoiceUrl(Pesanan $order): string
    {
        $secretKey = (string) config('services.xendit.secret_key');

        if ($secretKey === '') {
            throw new RuntimeException('XENDIT_SECRET_KEY belum dikonfigurasi.');
        }

        Configuration::setXenditKey($secretKey);
        $invoiceApi = new InvoiceApi();

        $invoice = $invoiceApi->createInvoice([
            'external_id' => Schema::hasColumn('pesanan', 'external_id')
                ? (string) $order->external_id
                : (string) $order->idpesanan,
            'amount' => (float) $order->total,
            'description' => 'Pembayaran pesanan #'.$order->idpesanan,
            'currency' => 'IDR',
            'invoice_duration' => 86400,
        ]);

        $invoiceUrl = (string) $invoice->getInvoiceUrl();
        if ($invoiceUrl === '') {
            throw new RuntimeException('URL invoice Xendit tidak diterima.');
        }

        return $invoiceUrl;
    }

    private function previewGuestId(): string
    {
        $lastUserId = (int) (User::query()->max('id') ?? 0);

        return $this->formatGuestName($lastUserId + 1);
    }

    private function formatGuestName(int $numericId): string
    {
        return sprintf('Guest_%07d', $numericId);
    }

    private function syncUsersSequence(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("SELECT setval(pg_get_serial_sequence('users','id'), COALESCE((SELECT MAX(id) FROM users), 1), true)");
    }

    private function ensureKantinTables(): void
    {
        foreach (['vendor', 'menu', 'pesanan', 'detail_pesanan'] as $table) {
            if (! Schema::hasTable($table)) {
                abort(500, "Tabel {$table} belum tersedia di database.");
            }
        }
    }
}