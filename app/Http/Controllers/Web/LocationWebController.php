<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocationWebController extends Controller
{
    public function index(): View
    {
        $locations = Location::query()
            ->withCount(['assets', 'users'])
            ->latest('id')
            ->paginate(15);

        return view('locations.index', [
            'locations' => $locations,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:locations,name'],
            'address' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        Location::create($validated);

        return redirect()->route('web.locations.index')->with('success', 'Lokasi kantor cabang berhasil ditambahkan!');
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:locations,name,'.$location->id],
            'address' => ['required', 'string'],
            'description' => ['nullable', 'string'],
        ]);

        $location->update($validated);

        return redirect()->route('web.locations.index')->with('success', 'Data lokasi berhasil diperbarui!');
    }

    public function destroy(Location $location): RedirectResponse
    {
        if ($location->assets()->exists()) {
            return redirect()->route('web.locations.index')->with('error', 'Gagal menghapus: Masih terdapat aset fisik yang terdaftar pada lokasi ini.');
        }

        if ($location->users()->exists()) {
            return redirect()->route('web.locations.index')->with('error', 'Gagal menghapus: Masih terdapat pengguna/karyawan yang bertugas pada lokasi ini.');
        }

        $location->delete();

        return redirect()->route('web.locations.index')->with('success', 'Lokasi berhasil dihapus.');
    }
}
