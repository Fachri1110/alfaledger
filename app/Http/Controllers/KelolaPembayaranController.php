<?php

namespace App\Http\Controllers;

use App\Models\Jenjang;
use App\Models\Golongan;
use App\Models\SettingItemUangPangkal;
use App\Models\SettingSpp;
use Illuminate\Http\Request;

class KelolaPembayaranController extends Controller
{
    protected function getSdJenjangId()
    {
        $jenjang = Jenjang::where('nama_jenjang', 'SD')->first();
        return $jenjang ? $jenjang->id : null;
    }

    /**
     * Display & API for Kelola Uang Pangkal
     */
    public function indexPangkal(Request $request)
    {
        $jenjangId = $this->getSdJenjangId();
        $kelasList = ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
        $kategoriList = ['REG', 'ANP', 'APK', 'FULL', 'HALF'];

        $selectedKelas = $request->get('kelas', $kelasList[0]);
        $selectedKategori = $request->get('kategori', $kategoriList[0]);

        $defaultNames = [
            1 => 'Gedung', 2 => 'Materi', 3 => 'Praktek',
            4 => 'Kegiatan', 5 => 'Ekstrakurikuler', 6 => 'Komputer',
            7 => 'Administrasi', 8 => 'Raport', 9 => 'Badge',
        ];

        $items = [];
        for ($i = 1; $i <= 9; $i++) {
            $setting = SettingItemUangPangkal::firstOrCreate(
                [
                    'jenjang_id' => $jenjangId,
                    'kelas'      => $selectedKelas,
                    'kategori'   => $selectedKategori,
                    'item_index' => $i,
                ],
                [
                    'nama_item' => $defaultNames[$i],
                    'nominal'   => 0,
                    'is_aktif'  => false,
                ]
            );

            $nominal = (float) $setting->nominal;
            $items[] = [
                'id'             => $setting->id,
                'item_index'     => $i,
                'label'          => $setting->nama_item ?: $defaultNames[$i],
                'amount'         => $nominal,
                'display_amount' => number_format($nominal, 0, ',', '.'),
                'aktif'          => (bool) $setting->is_aktif,
            ];
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'items' => $items,
            ]);
        }

        return view('admin.sd.partials.kelola_uangpangkal', compact('kelasList', 'kategoriList', 'items', 'selectedKelas', 'selectedKategori'));
    }

    /**
     * Save/Update Uang Pangkal Settings
     */
    public function updatePangkal(Request $request)
    {
        $request->validate([
            'kelas'              => 'required|string',
            'kategori'           => 'required|string',
            'items'              => 'required|array',
            'items.*.item_index' => 'required|integer',
            'items.*.amount'     => 'required|numeric|min:0',
            'items.*.aktif'      => 'required|boolean',
        ]);

        $jenjangId = $this->getSdJenjangId();
        $kelas = $request->kelas;
        $kategori = $request->kategori;
        $totalAktifNominal = 0;

        $defaultNames = [
            1 => 'Gedung', 2 => 'Materi', 3 => 'Praktek',
            4 => 'Kegiatan', 5 => 'Ekstrakurikuler', 6 => 'Komputer',
            7 => 'Administrasi', 8 => 'Raport', 9 => 'Badge',
        ];

        foreach ($request->items as $itemData) {
            SettingItemUangPangkal::updateOrCreate(
                [
                    'jenjang_id' => $jenjangId,
                    'kelas'      => $kelas,
                    'kategori'   => $kategori,
                    'item_index' => $itemData['item_index'],
                ],
                [
                    'nama_item' => $defaultNames[$itemData['item_index']] ?? 'Item Uang Pangkal',
                    'nominal'  => $itemData['amount'],
                    'is_aktif' => $itemData['aktif'],
                ]
            );

            if ($itemData['aktif']) {
                $totalAktifNominal += $itemData['amount'];
            }
        }

        // Sync with Golongan table if REG category
        if (($kategori === 'REG' || $kategori === 'Reguler') && $totalAktifNominal > 0) {
            Golongan::where('jenjang_id', $jenjangId)
                ->where('nama_golongan', $kelas)
                ->update(['nominal_uang_pangkal' => $totalAktifNominal]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Uang Pangkal berhasil disimpan.',
        ]);
    }

    /**
     * Display & API for Kelola SPP
     */
    public function indexSpp(Request $request)
    {
        $jenjangId = $this->getSdJenjangId();
        $kelasList = ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
        $selectedKelas = $request->get('kelas', $kelasList[0]);

        $categories = ['REG', 'ANP'];
        $items = [];

        foreach ($categories as $cat) {
            $legacyName = $cat === 'REG' ? 'Reguler' : 'Anak Pegawai';

            // Cari record canonical ('ANP' / 'REG') terlebih dahulu
            $canonical = SettingSpp::where('jenjang_id', $jenjangId)
                ->where('kelas', $selectedKelas)
                ->where('kategori', $cat)
                ->first();

            // Cari record legacy ('Anak Pegawai' / 'Reguler')
            $legacy = SettingSpp::where('jenjang_id', $jenjangId)
                ->where('kelas', $selectedKelas)
                ->where('kategori', $legacyName)
                ->first();

            if ($canonical) {
                // Sudah ada record canonical — hapus legacy jika ada (bersihkan duplikat)
                if ($legacy) {
                    $legacy->delete();
                }
                $setting = $canonical;
            } elseif ($legacy) {
                // Hanya ada record legacy — rename ke canonical
                $legacy->kategori = $cat;
                $legacy->save();
                $setting = $legacy;
            } else {
                // Tidak ada sama sekali — buat baru
                $setting = SettingSpp::create([
                    'jenjang_id' => $jenjangId,
                    'kelas'      => $selectedKelas,
                    'kategori'   => $cat,
                    'nominal'    => 0,
                ]);
            }

            $nominal = (float) $setting->nominal;
            $items[] = [
                'id'             => $setting->id,
                'label'          => $cat,
                'amount'         => $nominal,
                'display_amount' => number_format($nominal, 0, ',', '.'),
            ];
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'items' => $items,
            ]);
        }

        return view('admin.sd.partials.kelola_uangspp', compact('kelasList', 'items', 'selectedKelas'));
    }

    /**
     * Save/Update SPP Settings
     */
    public function updateSpp(Request $request)
    {
        $request->validate([
            'kelas' => 'required|string',
            'items' => 'required|array',
            'items.*.label' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        $jenjangId = $this->getSdJenjangId();
        $kelas = $request->kelas;

        foreach ($request->items as $itemData) {
            SettingSpp::updateOrCreate(
                [
                    'jenjang_id' => $jenjangId,
                    'kelas' => $kelas,
                    'kategori' => $itemData['label'],
                ],
                [
                    'nominal' => $itemData['amount'],
                ]
            );

            // Sync Reguler SPP nominal to Golongan table
            if ($itemData['label'] === 'REG' || $itemData['label'] === 'Reguler') {
                Golongan::where('jenjang_id', $jenjangId)
                    ->where('nama_golongan', $kelas)
                    ->update(['nominal_uang_spp' => $itemData['amount']]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data SPP berhasil disimpan.',
        ]);
    }
}
