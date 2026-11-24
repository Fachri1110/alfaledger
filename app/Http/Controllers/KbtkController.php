<?php

namespace App\Http\Controllers;

use App\Services\PembayaranService;
use App\Models\Jenjang;
use App\Models\Golongan;
use Illuminate\Http\Request;

/**
 * KbtkController
 * 
 * Handles KB/TK dashboard and payment operations
 */
class KbtkController extends Controller
{
    protected $pembayaranService;
    protected $jenjangId;
    protected $jenjangName = 'KB/TK';

    public function __construct(PembayaranService $pembayaranService)
    {
        $this->pembayaranService = $pembayaranService;
        
        // Get KB/TK jenjang ID
        $jenjang = Jenjang::where('nama_jenjang', 'KB/TK')->first();
        $this->jenjangId = $jenjang ? $jenjang->id : null;
    }

    /**
     * Display KB/TK dashboard
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        if (!$this->jenjangId) {
            abort(404, 'Jenjang KB/TK tidak ditemukan');
        }

        // Get kelas filter from request (kb, tka, tkb, or null for all)
        $kelasFilter = $request->get('kelas');
        $golonganId = null;

        // Map kelas to golongan_id
        if ($kelasFilter) {
            $golonganId = $this->mapKelasToGolongan($kelasFilter);
        }

        // Get statistics
        $stats = $this->pembayaranService->getStatsDashboard(
            jenjangId: $this->jenjangId,
            golonganId: $golonganId
        );

        // Get recent transactions
        $transaksi = $this->pembayaranService->getTransaksiTerbaru(
            jenjangId: $this->jenjangId,
            limit: 10,
            golonganId: $golonganId
        );

        // Get available classes for filter
        $kelasOptions = Golongan::where('jenjang_id', $this->jenjangId)
            ->orderBy('nama_golongan')
            ->get()
            ->map(function ($golongan) {
                return [
                    'value' => $this->mapGolonganToKelas($golongan->nama_golongan),
                    'label' => $golongan->nama_golongan,
                    'id' => $golongan->id,
                ];
            });

        return view('admin.kbtk.dashboard', compact(
            'stats',
            'transaksi',
            'kelasOptions',
            'kelasFilter'
        ));
    }

    /**
     * Map kelas parameter to golongan_id
     * 
     * @param string $kelas
     * @return int|null
     */
    private function mapKelasToGolongan($kelas)
    {
        $mapping = [
            'kb' => 'KB',
            'tka' => 'TK A',
            'tkb' => 'TK B',
        ];

        $namaGolongan = $mapping[$kelas] ?? null;
        
        if ($namaGolongan) {
            $golongan = Golongan::where('jenjang_id', $this->jenjangId)
                ->where('nama_golongan', $namaGolongan)
                ->first();
            return $golongan ? $golongan->id : null;
        }

        return null;
    }

    /**
     * Map golongan name to kelas parameter
     * 
     * @param string $namaGolongan
     * @return string
     */
    private function mapGolonganToKelas($namaGolongan)
    {
        $reverseMapping = [
            'KB' => 'kb',
            'TK A' => 'tka',
            'TK B' => 'tkb',
        ];

        return $reverseMapping[$namaGolongan] ?? strtolower(str_replace(' ', '', $namaGolongan));
    }
}
