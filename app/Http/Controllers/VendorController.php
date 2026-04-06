<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{
    public function index()
    {
        $this->authorizeLoggedInUser();

        $this->ensureKantinTables();

        $vendors = Vendor::query()
            ->orderBy('nama_vendor')
            ->get();

        $menus = Menu::query()
            ->with('vendor')
            ->orderByDesc('idmenu')
            ->get();

        return view('kantin.vendor.index', [
            'vendors' => $vendors,
            'menus' => $menus,
            'editingMenu' => null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeLoggedInUser();

        $this->ensureKantinTables();

        $validated = $request->validate([
            'idvendor' => ['required', 'integer', 'exists:vendor,idvendor'],
            'nama_menu' => ['required', 'string', 'max:255'],
            'harga' => ['required', 'integer', 'min:1'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $pathGambar = null;
        if ($request->hasFile('foto')) {
            $pathGambar = $request->file('foto')->store('menu', 'public');
        }

        Menu::query()->create([
            'idvendor' => (int) $validated['idvendor'],
            'nama_menu' => $validated['nama_menu'],
            'harga' => (int) $validated['harga'],
            'path_gambar' => $pathGambar,
        ]);

        return redirect()->route('vendor.index')->with('success', 'Menu baru berhasil ditambahkan.');
    }

    public function edit($idmenu)
    {
        $this->authorizeLoggedInUser();

        $this->ensureKantinTables();

        $vendors = Vendor::query()
            ->orderBy('nama_vendor')
            ->get();

        $menus = Menu::query()
            ->with('vendor')
            ->orderByDesc('idmenu')
            ->get();

        $editingMenu = Menu::query()->findOrFail($idmenu);

        return view('kantin.vendor.index', compact('vendors', 'menus', 'editingMenu'));
    }

    public function update(Request $request, $idmenu)
    {
        $this->authorizeLoggedInUser();

        $this->ensureKantinTables();

        $menu = Menu::query()->findOrFail($idmenu);

        $validated = $request->validate([
            'idvendor' => ['required', 'integer', 'exists:vendor,idvendor'],
            'nama_menu' => ['required', 'string', 'max:255'],
            'harga' => ['required', 'integer', 'min:1'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $pathGambar = $menu->path_gambar;

        if ($request->hasFile('foto')) {
            if ($pathGambar && Storage::disk('public')->exists($pathGambar)) {
                Storage::disk('public')->delete($pathGambar);
            }
            $pathGambar = $request->file('foto')->store('menu', 'public');
        }

        $menu->update([
            'idvendor' => (int) $validated['idvendor'],
            'nama_menu' => $validated['nama_menu'],
            'harga' => (int) $validated['harga'],
            'path_gambar' => $pathGambar,
        ]);

        return redirect()->route('vendor.index')->with('success', 'Data menu berhasil diperbarui.');
    }

    public function destroy($idmenu)
    {
        $this->authorizeLoggedInUser();

        $this->ensureKantinTables();

        $menu = Menu::query()->findOrFail($idmenu);

        if ($menu->path_gambar && Storage::disk('public')->exists($menu->path_gambar)) {
            Storage::disk('public')->delete($menu->path_gambar);
        }

        $menu->delete();

        return redirect()->route('vendor.index')->with('success', 'Menu berhasil dihapus.');
    }

    public function orders()
    {
        $this->authorizeLoggedInUser();

        $this->ensureKantinTables();

        $orders = Pesanan::query()
            ->with('detail_pesanan.menu')
            ->where('status_bayar', 1)
            ->orderByDesc('timestamp')
            ->get();

        return view('kantin.vendor.orders', compact('orders'));
    }

    private function ensureKantinTables(): void
    {
        $requiredTables = ['vendor', 'menu', 'pesanan', 'detail_pesanan'];

        foreach ($requiredTables as $table) {
            if (! Schema::hasTable($table)) {
                abort(500, "Tabel {$table} belum tersedia di database.");
            }
        }
    }

    private function authorizeLoggedInUser(): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403, 'Akses ditolak.');
        }
    }
}
