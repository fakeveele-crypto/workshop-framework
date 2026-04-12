<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
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

    public function finish(Request $request, int $idpesanan): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $this->ensureKantinTables();

        $order = Pesanan::query()->find($idpesanan);

        if (! $order) {
            return redirect()->route('customer.index')->with('error', 'Pesanan tidak ditemukan.');
        }

        $qrcode = QrCode::format('svg')
            ->size(260)
            ->margin(1)
            ->generate((string) $order->idpesanan);

        return view('kantin.customer.pembayaran_selesai', [
            'order' => $order,
            'qrcode' => $qrcode,
            'paymentConfirmed' => (int) $order->status_bayar === 1,
        ]);
    }

    public function dataCustomer(): \Illuminate\Contracts\View\View
    {
        $this->ensureCustomerTable();

        $customers = Customer::query()
            ->orderByDesc('id')
            ->get();

        $customers->each(function (Customer $customer): void {
            $customer->foto_blob_preview = $this->blobToDataUrl($customer->foto_blob);
            $customer->foto_path_url = null;

            if ($customer->foto_path && Storage::disk('public')->exists($customer->foto_path)) {
                $customer->foto_path_url = Storage::url($customer->foto_path);
            }
        });

        return view('customer.index', [
            'customers' => $customers,
        ]);
    }

    public function createCustomerBlob(): \Illuminate\Contracts\View\View
    {
        $this->ensureCustomerTable();

        return view('customer.create_blob');
    }

    public function storeCustomerBlob(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->ensureCustomerTable();

        $validated = $this->validateCustomerPayload($request);

        try {
            $snapshot = $this->decodeSnapshot((string) $validated['foto_snapshot']);

            Customer::query()->create([
                'nama' => $validated['nama'],
                'alamat' => $validated['alamat'],
                'provinsi' => $validated['provinsi'],
                'kota' => $validated['kota'],
                'kecamatan' => $validated['kecamatan'],
                'kodepos_kelurahan' => $validated['kodepos_kelurahan'],
                'foto_blob' => $this->normalizeBlobForDatabase($snapshot['binary']),
                'foto_path' => null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data customer. Silakan coba ambil foto ulang.');
        }

        return redirect()
            ->route('customer_data.index')
            ->with('success', 'Data customer berhasil disimpan sebagai BYTEA.');
    }

    public function createCustomerPath(): \Illuminate\Contracts\View\View
    {
        $this->ensureCustomerTable();

        return view('customer.create_path');
    }

    public function storeCustomerPath(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->ensureCustomerTable();

        $validated = $this->validateCustomerPayload($request);

        try {
            $snapshot = $this->decodeSnapshot((string) $validated['foto_snapshot']);

            $filename = 'customer_'.now()->format('YmdHis').'_'.Str::lower(Str::random(8)).'.'.$snapshot['extension'];
            $storedPath = 'customer/'.$filename;

            Storage::disk('public')->put($storedPath, $snapshot['binary']);

            Customer::query()->create([
                'nama' => $validated['nama'],
                'alamat' => $validated['alamat'],
                'provinsi' => $validated['provinsi'],
                'kota' => $validated['kota'],
                'kecamatan' => $validated['kecamatan'],
                'kodepos_kelurahan' => $validated['kodepos_kelurahan'],
                'foto_blob' => null,
                'foto_path' => $storedPath,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan data customer. Silakan coba ambil foto ulang.');
        }

        return redirect()
            ->route('customer_data.index')
            ->with('success', 'Data customer berhasil disimpan sebagai file path.');
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
            'success_redirect_url' => route('customer.pembayaran_selesai', ['idpesanan' => $order->idpesanan]),
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

    private function ensureCustomerTable(): void
    {
        if (! Schema::hasTable('customer')) {
            abort(500, 'Tabel customer belum tersedia di database.');
        }
    }

    /**
     * @return array{nama:string,alamat:string,provinsi:string,kota:string,kecamatan:string,kodepos_kelurahan:string,foto_snapshot:string}
     */
    private function validateCustomerPayload(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'kodepos_kelurahan' => ['required', 'string', 'max:50'],
            'foto_snapshot' => ['required', 'string'],
        ]);
    }

    /**
     * @return array{binary:string,extension:string}
     */
    private function decodeSnapshot(string $snapshotDataUrl): array
    {
        if (! preg_match('/^data:image\/(png|jpe?g|webp);base64,(.+)$/i', $snapshotDataUrl, $matches)) {
            throw new RuntimeException('Format foto tidak valid. Ambil foto ulang dari kamera.');
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            throw new RuntimeException('Data foto tidak valid.');
        }

        $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);

        return [
            'binary' => $binary,
            'extension' => $extension,
        ];
    }

    private function blobToDataUrl(mixed $blob): ?string
    {
        if ($blob === null) {
            return null;
        }

        $binary = null;

        if (is_resource($blob)) {
            $binary = stream_get_contents($blob) ?: null;
        } elseif (is_string($blob)) {
            if (str_starts_with($blob, '\\x')) {
                $binary = hex2bin(substr($blob, 2)) ?: null;
            } else {
                $binary = $blob;
            }
        }

        if (! is_string($binary) || $binary === '') {
            return null;
        }

        return 'data:image/jpeg;base64,'.base64_encode($binary);
    }

    private function normalizeBlobForDatabase(string $binary): string
    {
        if (DB::getDriverName() === 'pgsql') {
            return '\\x'.bin2hex($binary);
        }

        return $binary;
    }
}