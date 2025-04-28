<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::all();
        return view('admin.brand.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_brand' => 'required|string|max:25',
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048' // Optional logo upload
        ]);

        $kode_brand = Brand::generateKodeBrand($request->nama_brand);
        $brandData = [
            'kode_brand' => $kode_brand,
            'nama_brand' => $request->nama_brand
        ];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');

            // Generate unique filename with kode_brand and upload date
            $filename = $kode_brand . '_' . now()->format('Ymd') . '.' . $logo->getClientOriginalExtension();

            // Store in brand folder
            $logoPath = $logo->storeAs('brand', $filename, 'public');

            $brandData['logo'] = $logoPath;
        }

        Brand::create($brandData);

        return back()->with('success', 'Brand berhasil ditambahkan');
    }

    public function update(Request $request, $kode_brand)
    {
        $request->validate([
            'nama_brand' => 'required|string|max:25',
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048' // Optional logo upload
        ]);

        $brand = Brand::findOrFail($kode_brand);
        $brandData = ['nama_brand' => $request->nama_brand];

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete existing logo if it exists
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }

            $logo = $request->file('logo');

            // Generate unique filename with kode_brand and upload date
            $filename = $kode_brand . '_' . now()->format('Ymd') . '.' . $logo->getClientOriginalExtension();

            // Store in brand folder
            $logoPath = $logo->storeAs('brand', $filename, 'public');

            $brandData['logo'] = $logoPath;
        } elseif ($request->has('existing_logo')) {
            // Retain existing logo
            $brandData['logo'] = $request->input('existing_logo');
        }

        $brand->update($brandData);
        return back()->with('success', 'Brand berhasil diperbarui');
    }

    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            $brand->delete();

            // Menggunakan with() untuk flash session
            return redirect()->route('brand.index')->with('success', 'Brand berhasil dihapus');
        } catch (\Exception $e) {
            // Jika gagal, kembalikan dengan pesan error
            return redirect()->route('brand.index')->with('error', 'Gagal menghapus brand');
        }
    }
}
