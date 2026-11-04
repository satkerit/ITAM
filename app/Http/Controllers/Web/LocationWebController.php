<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'kdloc' => ['nullable', 'string', 'max:50', 'unique:locations,kdloc'],
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
            'kdloc' => ['nullable', 'string', 'max:50', 'unique:locations,kdloc,'.$location->id],
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

    /**
     * Unduh template CSV/Excel untuk impor lokasi.
     */
    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['kdloc', 'name', 'address', 'description']);
            fputcsv($out, ['KC-SDR', 'Kantor Pusat Sudirman', 'Jl. Jend. Sudirman Kav. 1, Jakarta Selatan', 'Gedung Menara Lantai 12-15']);
            fputcsv($out, ['KC-BDG', 'Kantor Cabang Bandung', 'Jl. Asia Afrika No. 10, Bandung', 'Area operasional regional Jawa Barat']);
            fclose($out);
        }, 'template-lokasi-kantor.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Impor data lokasi dari file CSV.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $rows = $this->parseCsv($request->file('file')->getRealPath());
        $created = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $kdloc = trim((string) ($row['kdloc'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $address = trim((string) ($row['address'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));

            if ($name === '' || $address === '') {
                $skipped++;

                continue;
            }

            if (Location::where('name', $name)->exists() || ($kdloc !== '' && Location::where('kdloc', $kdloc)->exists())) {
                $skipped++;

                continue;
            }

            Location::create([
                'kdloc' => $kdloc !== '' ? $kdloc : null,
                'name' => $name,
                'address' => $address,
                'description' => $description !== '' ? $description : null,
            ]);
            $created++;
        }

        return redirect()->route('web.locations.index')
            ->with('success', "Impor selesai: {$created} lokasi kantor ditambahkan, {$skipped} baris dilewati (duplikat/invalid).");
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);

            return $rows;
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

        while (($line = fgetcsv($handle)) !== false) {
            if (count(array_filter($line, fn ($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $rows[] = array_combine($header, array_pad($line, count($header), null));
        }

        fclose($handle);

        return $rows;
    }
}
