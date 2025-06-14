<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Tipe;
use App\Models\Brand;
use App\Models\Warna;
use App\Models\Barang;
use Illuminate\Support\Str;
use App\Models\DetailBarang;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function index()
    {
        $barangs = Barang::with('tipe')->get();
        return view('admin.barang.index', compact('barangs'));
    }
    
    public function barang(Request $request)
    {
        // Get all brands for filter dropdown
        $brands = Brand::orderBy('nama_brand')->get();
        
        // Get selected brand if any
        $selectedBrand = null;
        if ($request->brand) {
            $selectedBrand = Brand::where('kode_brand', $request->brand)->first();
        }
        
        // Start building the query with relationships
        $query = Barang::with(['tipe.brand']);
        
        // Apply brand filter through tipe relationship
        if ($request->brand) {
            $query->whereHas('tipe', function($q) use ($request) {
                $q->where('kode_brand', $request->brand);
            });
        }
        
        // Apply price filter - Perbaiki nama kolom
        if ($request->min_price) {
            $query->where('harga_normal', '>=', $request->min_price);
        }
        
        if ($request->max_price) {
            $query->where('harga_normal', '<=', $request->max_price);
        }
        
        // Apply sorting
        $sort = $request->get('sort', 'terbaru');
        switch ($sort) {
            case 'harga_rendah':
                $query->orderBy('harga_normal', 'ASC');
                break;
            case 'harga_tinggi':
                $query->orderBy('harga_normal', 'DESC');
                break;
            case 'nama_asc':
                $query->orderBy('nama_barang', 'asc');
                break;
            case 'nama_desc':
                $query->orderBy('nama_barang', 'desc');
                break;
            case 'terbaru':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        // Filter hanya barang yang aktif
        $query->where('is_active', 1);
        
        // Get total count before pagination
        $totalBarang = Barang::where('is_active', 1)->count();
        
        // Paginate results
        $barang = $query->paginate(12);
        
        // Add additional properties to each item
        $barang->getCollection()->transform(function ($item) {
            // Format price - gunakan kolom yang benar
            $item->formatted_harga = 'Rp ' . number_format($item->harga_normal, 0, ',', '.');
            
            // Mock data untuk properties yang tidak ada di database
            $item->discount_percentage = 0;
            $item->is_terlaris = false;
            $item->rating = rand(35, 50) / 10;
            $item->review_count = rand(10, 200);
            
            return $item;
        });
        
        return view('pelanggan.barang.index', compact('barang', 'brands', 'selectedBrand', 'totalBarang'));
    }
    
    public function byBrand($brandId)
    {
        // Redirect ke route yang benar
        return redirect()->route('pelanggan.barang', ['brand' => $brandId]);
    }


    public function create()
    {
        $tipes = Tipe::all();
        $warnas = Warna::all();
        $ukurans = [
            '40.0' => '40',
            '41.5' => '41.5',
            '42.0' => '42',
            '43.0' => '43'
        ];
        return view('admin.barang.form', compact('tipes', 'warnas', 'ukurans'));
    }

    // Update method store di BarangController
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_barang' => 'required|max:100',
            'berat' => 'required|integer|min:1',
            'harga_normal' => 'required|integer|min:1000',
            'deskripsi' => 'nullable',
            'gambar' => 'nullable|image|max:2048',
            'kode_tipe' => 'required|exists:tipe,kode_tipe',
            'source' => 'nullable|string', // untuk mendeteksi dari mana request berasal
        ]);

        try {
            DB::beginTransaction();

            // Generate kode barang
            $kode_barang = Barang::generateKodeBarang($validatedData['kode_tipe']);

            // Upload gambar jika ada
            $gambar_path = null;
            if ($request->hasFile('gambar')) {
                $gambar_path = $request->file('gambar')->store('barang_images', 'public');
            }

            // Simpan barang
            $barang = Barang::create([
                'kode_barang' => $kode_barang,
                'nama_barang' => $validatedData['nama_barang'],
                'berat' => $validatedData['berat'],
                'harga_normal' => $validatedData['harga_normal'],
                'deskripsi' => $validatedData['deskripsi'],
                'gambar' => $gambar_path,
                'kode_tipe' => $validatedData['kode_tipe']
            ]);

            if ($request->has('detail_warnas')) {
                $detailsToCreate = [];
                $kodeWarnaCounter = [];

                foreach ($request->input('detail_warnas') as $detail) {
                    $warna = $detail['kode_warna'];
                    $key = "{$kode_barang}-{$warna}";

                    if (!isset($kodeWarnaCounter[$key])) {
                        $existingCount = DetailBarang::where('kode_barang', $kode_barang)
                            ->where('kode_warna', $warna)
                            ->count();
                        $kodeWarnaCounter[$key] = $existingCount;
                    }
                  

                    $kodeWarnaCounter[$key]++;

                    $kode_detail = DetailBarang::generateKodeDetail($kode_barang, $warna, $kodeWarnaCounter[$key]);

                    $detailsToCreate[] = [
                        'kode_detail' => $kode_detail,
                        'kode_barang' => $kode_barang,
                        'kode_warna' => $warna,
                        'ukuran' => $detail['ukuran'],
                        // 'stok' => $detail['stok'] ?? 0
                    ];
                }
                Log::debug('Details to create:', $detailsToCreate);

                if (!empty($detailsToCreate)) {
                    DetailBarang::insert($detailsToCreate);
                }
            }

            DB::commit();

            // Cek dari mana request berasal
            if ($request->input('source') === 'barang-masuk') {
                // Jika dari modal barang-masuk, return JSON response
                return response()->json([
                    'success' => true,
                    'message' => 'Barang berhasil ditambahkan',
                    'data' => $barang
                ]);
            } else {
                // Jika dari form biasa, redirect ke index
                return redirect()->route('barang.index')
                    ->with('success', 'Barang berhasil ditambahkan');
            }
        } catch (Exception $e) {
            DB::rollback();

            // Hapus file gambar jika ada error
            if (isset($gambar_path) && Storage::exists($gambar_path)) {
                Storage::delete($gambar_path);
            }

            if ($request->input('source') === 'barang-masuk') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            } else {
                return redirect()->back()->withInput()
                    ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
            }
        }
    }

    public function storeDetail(Request $request)
    {
        $validatedData = $request->validate([
            'kode_barang' => 'required|exists:barang,kode_barang',
            'detail_warnas' => 'required|array|min:1',
            'detail_warnas.*.kode_warna' => 'required|exists:warna,kode_warna',
            'detail_warnas.*.ukuran' => 'required|string',
            'detail_warnas.*.stok' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $kodeBarang = $validatedData['kode_barang'];
            $detailsToCreate = [];
            $kodeWarnaCounter = [];

            // Validasi duplikasi warna + ukuran
            $existingDetails = DetailBarang::where('kode_barang', $kodeBarang)
                ->get()
                ->keyBy(function ($item) {
                    return $item->kode_warna . '-' . $item->ukuran;
                });

            foreach ($validatedData['detail_warnas'] as $detail) {
                $warna = $detail['kode_warna'];
                $ukuran = $detail['ukuran'];
                $key = $warna . '-' . $ukuran;

                // Cek duplikasi
                if ($existingDetails->has($key)) {
                    throw new Exception("Detail dengan warna dan ukuran {$ukuran} sudah ada");
                }

                // Generate kode detail
                $counterKey = "{$kodeBarang}-{$warna}";
                if (!isset($kodeWarnaCounter[$counterKey])) {
                    $existingCount = DetailBarang::where('kode_barang', $kodeBarang)
                        ->where('kode_warna', $warna)
                        ->count();
                    $kodeWarnaCounter[$counterKey] = $existingCount;
                }

                $kodeWarnaCounter[$counterKey]++;
                $kodeDetail = DetailBarang::generateKodeDetail($kodeBarang, $warna, $kodeWarnaCounter[$counterKey]);

                $detailsToCreate[] = [
                    'kode_detail' => $kodeDetail,
                    'kode_barang' => $kodeBarang,
                    'kode_warna' => $warna,
                    'ukuran' => $ukuran,
                    'stok' => $detail['stok'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if (!empty($detailsToCreate)) {
                DetailBarang::insert($detailsToCreate);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Detail barang berhasil ditambahkan',
                'data' => $detailsToCreate
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Method untuk mendapatkan detail barang berdasarkan kode_barang (optional, untuk debugging)
    public function getBarangDetails($kodeBarang)
    {
        try {
            $barang = Barang::with(['detailBarang.warna'])
                ->where('kode_barang', $kodeBarang)
                ->first();

            if (!$barang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $barang
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $kode_barang)
    {
        // Validasi input
        $request->validate([
            'nama_barang' => 'required|string|max:100',
            'kode_tipe' => 'required|string|exists:tipe,kode_tipe',
            'berat' => 'required|integer|min:1',
            'harga_normal' => 'required|integer|min:1000',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'detail_warnas' => 'nullable|array',
            'detail_warnas.*.kode_warna' => 'required_with:detail_warnas|string|exists:warna,kode_warna',
            'detail_warnas.*.ukuran' => 'required_with:detail_warnas|regex:/^\d+(\.\d+)?$/',
            'detail_warnas.*.stok' => 'required_with:detail_warnas|integer|min:0'
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi',
            'nama_barang.max' => 'Nama barang maksimal 100 karakter',
            'kode_tipe.required' => 'Tipe barang wajib dipilih',
            'kode_tipe.exists' => 'Tipe barang tidak valid',
            'berat.required' => 'Berat barang wajib diisi',
            'berat.min' => 'Berat minimal 1 gram',
            'harga_normal.required' => 'Harga normal wajib diisi',
            'harga_normal.min' => 'Harga normal minimal Rp 1.000',
            'gambar.image' => 'File harus berupa gambar',
            'gambar.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'gambar.max' => 'Ukuran gambar maksimal 2MB',
            'detail_warnas.*.kode_warna.required_with' => 'Warna wajib dipilih',
            'detail_warnas.*.kode_warna.exists' => 'Warna tidak valid',
            'detail_warnas.*.ukuran.required_with' => 'Ukuran wajib diisi',
            'detail_warnas.*.ukuran.regex' => 'Format ukuran tidak valid (contoh: 42 atau 41.5)',
            'detail_warnas.*.stok.required_with' => 'Stok wajib diisi',
            'detail_warnas.*.stok.min' => 'Stok tidak boleh negatif'
        ]);

        try {
            DB::beginTransaction();

            // Cari barang berdasarkan kode_barang
            $barang = Barang::where('kode_barang', $kode_barang)->firstOrFail();

            // Siapkan data untuk update
            $updateData = [
                'nama_barang' => $request->nama_barang,
                'kode_tipe' => $request->kode_tipe,
                'berat' => $request->berat,
                'harga_normal' => $request->harga_normal,
                'deskripsi' => $request->deskripsi,
                'updated_at' => now()
            ];

            // Handle upload gambar jika ada
            if ($request->hasFile('gambar')) {
                // Hapus gambar lama jika ada
                if ($barang->gambar && Storage::exists($barang->gambar)) {
                    Storage::delete($barang->gambar);
                }

                // Upload gambar baru
                $gambarPath = $request->file('gambar')->store('images/barang', 'public');
                $updateData['gambar'] = $gambarPath;
            }

            // Update data barang
            $barang->update($updateData);

            // Update detail barang jika ada
            if ($request->has('detail_warnas') && is_array($request->detail_warnas)) {
                // Ambil detail barang yang ada
                $existingDetails = DetailBarang::where('kode_barang', $kode_barang)->get();

                // Kumpulkan detail baru dari request
                $newDetails = collect($request->detail_warnas)->filter(function ($detail) {
                    return !empty($detail['kode_warna']) && !empty($detail['ukuran']) && isset($detail['stok']);
                });

                // Update atau buat detail baru
                foreach ($newDetails as $index => $detail) {
                    $existingDetail = $existingDetails->get($index);

                    if ($existingDetail) {
                        // Update detail yang sudah ada
                        $existingDetail->update([
                            'kode_warna' => $detail['kode_warna'],
                            'ukuran' => $detail['ukuran'],
                            'stok' => $detail['stok']
                        ]);
                    } else {
                        // Buat detail baru jika tidak ada
                        DetailBarang::create([
                            'kode_detail' => Str::uuid(), // atau generate kode kustom
                            'kode_barang' => $kode_barang,
                            'kode_warna' => $detail['kode_warna'],
                            'ukuran' => $detail['ukuran'],
                            'stok' => $detail['stok']
                        ]);
                        
                    }
                }

                // Hapus detail yang berlebih (yang tidak memiliki referensi foreign key)
                $detailsToDelete = $existingDetails->slice($newDetails->count());
                foreach ($detailsToDelete as $detailToDelete) {
                    // Cek apakah detail ini direferensi oleh tabel lain
                    $hasReference = DB::table('pemusnahan_barang')
                        ->where('kode_detail', $detailToDelete->kode_detail)
                        ->exists();

                    if (!$hasReference) {
                        $detailToDelete->delete();
                    } else {
                        // Jika ada referensi, tandai sebagai tidak aktif atau beri pesan warning
                        // Anda bisa menambahkan kolom is_active di tabel detail_barang
                        // $detailToDelete->update(['is_active' => 0]);

                        // Atau berikan pesan peringatan
                        session()->flash('warning', 'Beberapa detail barang tidak dapat dihapus karena masih digunakan dalam pemusnahan barang.');
                    }
                }
            }

            DB::commit();

            return redirect()->route('barang.index')
                ->with('success', 'Barang berhasil diperbarui');
        } catch (Exception $e) {
            DB::rollback();

            // Hapus gambar yang baru diupload jika ada error
            if (isset($gambarPath) && Storage::exists($gambarPath)) {
                Storage::delete($gambarPath);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui barang: ' . $e->getMessage());
        }
    }
    public function show($kode_barang)
    {
        $barang = Barang::with(['tipe', 'detailBarangs.warna'])->findOrFail($kode_barang);
        return view('admin.barang.show', compact('barang'));
    }

    public function edit($kode_barang)
    {
        $barang = Barang::with('detailBarangs')->findOrFail($kode_barang);
        $tipes = Tipe::all();
        $warnas = Warna::all();
        $ukurans = [
            '40.0' => '40',
            '41.5' => '41.5',
            '42.0' => '42',
            '43.0' => '43'
        ];

        return view('admin.barang.form', compact('barang', 'tipes', 'warnas', 'ukurans'));
    }


    public function destroy($kode_barang)
    {
        try {
            $barang = Barang::findOrFail($kode_barang);

            // Hapus gambar jika ada
            if ($barang->gambar) {
                Storage::disk('public')->delete($barang->gambar);
            }

            // Hapus detail barang terlebih dahulu
            DetailBarang::where('kode_barang', $kode_barang)->delete();

            // Hapus barang
            $barang->delete();

            return redirect()->route('barang.index')
                ->with('success', 'Barang berhasil dihapus');
        } catch (Exception $e) {
            return redirect()->route('barang.index')
                ->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }

    private function storeImage($file, $kode_barang)
    {
        // Simpan dengan nama kode barang
        $extension = $file->getClientOriginalExtension();
        $filename = $kode_barang . '.' . $extension;

        return $file->storeAs('barang', $filename, 'public');
    }
}
