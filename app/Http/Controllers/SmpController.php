<?php

namespace App\Http\Controllers;

use App\Services\PembayaranService;
use App\Models\Jenjang;
use App\Models\Golongan;
use Illuminate\Http\Request;

/**
 * SmpController
 * 
 * Handles SMP (Junior High School) dashboard and payment operations
 */
class SmpController extends Controller
{
    protected $pembayaranService;
    protected $jenjangId;
    protected $jenjangName = 'SMP';

    public function __construct(PembayaranService $pembayaranService)
    {
        $this->pembayaranService = $pembayaranService;
        
        // Get SMP jenjang ID
        $jenjang = Jenjang::where('nama_jenjang', 'SMP')->first();
        $this->jenjangId = $jenjang ? $jenjang->id : null;
    }

    /**
     * Display SMP dashboard
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        if (!$this->jenjangId) {
            abort(404, 'Jenjang SMP tidak ditemukan');
        }

        // Get kelas filter from request (7-9, or null for all)
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

        return view('admin.smp.dashboard', compact(
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
        $cleanKelas = trim(str_replace('Kelas', '', (string)$kelas));

        $golongan = Golongan::where('jenjang_id', $this->jenjangId)
            ->where(function($q) use ($kelas, $cleanKelas) {
                $q->where('nama_golongan', 'Kelas ' . $cleanKelas)
                  ->orWhere('nama_golongan', $kelas);
            })
            ->first();

        return $golongan ? $golongan->id : null;
    }

    /**
     * Map golongan name to kelas parameter
     * 
     * @param string $namaGolongan
     * @return string
     */
    private function mapGolonganToKelas($namaGolongan)
    {
        // Extract number from "Kelas 7", "Kelas 8", etc.
        if (preg_match('/Kelas (\d+)/', $namaGolongan, $matches)) {
            return $matches[1];
        }
        
        return strtolower(str_replace(' ', '', $namaGolongan));
    }
}
