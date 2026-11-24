<?php

namespace App\Http\Controllers;

use App\Services\PembayaranService;
use App\Models\Jenjang;
use App\Models\Golongan;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\Tahun_ajaran;
use App\Models\SettingItemUangPangkal;
use App\Models\ItemUangPangkal;
use App\Models\SettingSpp;
use App\Models\RiwayatTransaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * SdController
 *
 * Handles SD (Elementary School) dashboard and payment operations
 */
class SdController extends Controller
{
    protected $pembayaranService;
    protected $jenjangId;
    protected $jenjangName = 'SD';

    protected array $kelasList = ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'];
    protected array $bulanList = ['Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

    protected const BULAN_ABBR = [
        7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
    ];

    protected const BULAN_NOMOR = [
        'Jul' => 7, 'Ags' => 8, 'Sep' => 9, 'Okt' => 10, 'Nov' => 11, 'Des' => 12,
        'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'Mei' => 5, 'Jun' => 6,
    ];

    protected const SHORTHAND_KATEGORI = [
        'REG'  => 'Reguler',
        'ANP'  => 'Anak Pegawai',
        'APK'  => 'Anak Pegawai Khusus',
        'FULL' => 'Pindahan Satu Tahun',
        'HALF' => 'Pindahan Setengah Tahun',
    ];

    protected const DEFAULT_UAP_ITEMS = [
        1 => 'Gedung',       2 => 'Materi',     3 => 'Praktek',
        4 => 'Kegiatan',     5 => 'Ekstrakurikuler', 6 => 'Komputer',
        7 => 'Administrasi', 8 => 'Raport',     9 => 'Badge',
    ];

    public function __construct(PembayaranService $pembayaranService)
    {
        $this->pembayaranService = $pembayaranService;
        $jenjang = Jenjang::where('nama_jenjang', 'SD')->first();
        $this->jenjangId = $jenjang?->id;
    }

    private function getDefaultBulan(): string
    {
        return self::BULAN_ABBR[Carbon::now()->month] ?? $this->bulanList[0];
    }

    /**
     * Display SD dashboard
     */
    public function dashboard(Request $request)
    {
        if (!$this->jenjangId) abort(404, 'Jenjang SD tidak ditemukan');

        $kelasFilter = $request->get('kelas');
        
        if ($kelasFilter && in_array($kelasFilter, ['1', '2', '3', '4', '5', '6'])) {
            $golonganId = $this->mapKelasToGolongan($kelasFilter);
            
            $stats = $this->pembayaranService->getStatsDashboard(
                jenjangId: $this->jenjangId,
                golonganId: $golonganId
            );

            $transaksi = $this->pembayaranService->getTransaksiTerbaru(
                jenjangId: $this->jenjangId,
                limit: 10,
                golonganId: $golonganId
            );

            $mappedStats = [
                'uang_pangkal' => $stats['uang_pangkal_terbayar'] ?? 0,
                'spp'          => $stats['spp_lunas'] ?? 0,
                'total_siswa'  => $stats['total_siswa'] ?? 0,
            ];

            return view('admin.sd.kelas.dbkelas', [
                'kelas'       => 'Kelas ' . $kelasFilter,
                'stats'       => $mappedStats,
                'transaksi'   => $transaksi,
                'kelasFilter' => $kelasFilter,
            ]);
        }

        $stats = $this->pembayaranService->getStatsDashboard(jenjangId: $this->jenjangId);
        $transaksi = $this->pembayaranService->getTransaksiTerbaru(jenjangId: $this->jenjangId, limit: 10);

        $kelasOptions = Golongan::where('jenjang_id', $this->jenjangId)
            ->orderBy('nama_golongan')
            ->get()
            ->map(fn($g) => [
                'value' => $this->mapGolonganToKelas($g->nama_golongan),
                'label' => $g->nama_golongan,
                'id'    => $g->id,
            ]);

        return view('admin.sd.dashboard', compact('stats', 'transaksi', 'kelasOptions', 'kelasFilter'));
    }

    /**
     * Display Rekap Uang Pangkal
     */
    public function rekapUangPangkal(Request $request)
    {
        if (!$this->jenjangId) abort(404, 'Jenjang SD tidak ditemukan');

        $selectedKelas = $request->get('kelas', $this->kelasList[0]);
        $selectedBulan = $request->get('bulan', $this->getDefaultBulan());

        $data   = $this->buildRekapPangkalData($selectedKelas, $selectedBulan);
        $items  = $data['items'];
        $totals = $data['totals'];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'items' => $items, 'totals' => $totals]);
        }

        $kelasList = $this->kelasList;
        $bulanList = $this->bulanList;

        return view('admin.sd.partials.rekap_uap', compact(
            'kelasList', 'selectedKelas', 'bulanList', 'selectedBulan', 'items', 'totals'
        ));
    }

    private function buildRekapPangkalData(string $kelas, ?string $selectedBulan = null): array
    {
        $defaultNames = self::DEFAULT_UAP_ITEMS;
        $targetBulan  = $selectedBulan ? (self::BULAN_NOMOR[$selectedBulan] ?? null) : null;
        $golonganId   = $this->mapKelasToGolongan(str_replace('Kelas ', '', $kelas));
        $categories   = ['REG', 'ANP', 'APK', 'FULL', 'HALF'];

        $allSettings = SettingItemUangPangkal::where('jenjang_id', $this->jenjangId)
            ->where('kelas', $kelas)
            ->get()
            ->groupBy('kategori');

        $itemResult = [];
        foreach ($defaultNames as $index => $nama) {
            $itemResult[$index] = ['label' => $nama, 'reg' => 0, 'anp' => 0, 'apk' => 0, 'full' => 0, 'half' => 0];
        }

        foreach ($categories as $catCode) {
            $legacyName = self::SHORTHAND_KATEGORI[$catCode] ?? 'Reguler';
            $catKey     = strtolower($catCode);

            $catSettings = $allSettings->get($catCode, collect());
            if ($catSettings->isEmpty()) {
                $catSettings = $allSettings->get($legacyName, collect());
            }

            $settingNominals = [];
            $totalSettingNominal = 0;
            foreach ($defaultNames as $index => $nama) {
                $sItem = $catSettings->firstWhere('item_index', $index) ?? $catSettings->firstWhere('nama_item', $nama);
                $nom = (float) ($sItem?->nominal ?? 0);
                $settingNominals[$index] = $nom;
                $totalSettingNominal += $nom;
            }

            $baseQuery = Pembayaran::whereHas('tagihan', function ($q) use ($golonganId, $catCode, $legacyName) {
                $q->where('jenis', 'uang_pangkal')
                  ->whereHas('siswa', function ($sq) use ($golonganId, $catCode, $legacyName) {
                      $sq->where('golongan_id', $golonganId);
                      if ($catCode === 'REG') {
                          $sq->where(fn($w) => $w->whereIn('golongan_kategori', [$catCode, $legacyName])->orWhereNull('golongan_kategori')->orWhere('golongan_kategori', ''));
                      } else {
                          $sq->whereIn('golongan_kategori', [$catCode, $legacyName]);
                      }
                  });
            });

            if ($targetBulan) {
                $baseQuery->whereMonth('tanggal_bayar', $targetBulan);
            }

            $payments = $baseQuery->with('itemUangPangkal')->get();
            $itemizedTotals = array_fill(1, 9, 0.0);
            $unallocatedTotal = 0.0;

            foreach ($payments as $p) {
                $itemName = $p->itemUangPangkal?->nama_item;
                $matchedIndex = null;
                if ($itemName) {
                    foreach ($defaultNames as $idx => $defName) {
                        if (strcasecmp($itemName, $defName) === 0) {
                            $matchedIndex = $idx;
                            break;
                        }
                    }
                }

                if ($matchedIndex) {
                    $itemizedTotals[$matchedIndex] += (float) $p->jumlah_bayar;
                } else {
                    $unallocatedTotal += (float) $p->jumlah_bayar;
                }
            }

            if ($unallocatedTotal > 0) {
                if ($totalSettingNominal > 0) {
                    $allocatedSoFar = 0;
                    $lastNonZeroIndex = null;
                    foreach ($defaultNames as $index => $nama) {
                        if ($settingNominals[$index] > 0) $lastNonZeroIndex = $index;
                    }

                    foreach ($defaultNames as $index => $nama) {
                        if ($settingNominals[$index] <= 0) continue;
                        if ($index === $lastNonZeroIndex) {
                            $share = $unallocatedTotal - $allocatedSoFar;
                        } else {
                            $ratio = $settingNominals[$index] / $totalSettingNominal;
                            $share = round($unallocatedTotal * $ratio, 2);
                            $allocatedSoFar += $share;
                        }
                        $itemizedTotals[$index] += $share;
                    }
                } else {
                    $itemizedTotals[1] += $unallocatedTotal;
                }
            }

            foreach ($defaultNames as $index => $nama) {
                $itemResult[$index][$catKey] = round($itemizedTotals[$index], 2);
            }
        }

        $items = array_values($itemResult);
        $totals = [
            'reg'  => array_sum(array_column($items, 'reg')),
            'anp'  => array_sum(array_column($items, 'anp')),
            'apk'  => array_sum(array_column($items, 'apk')),
            'full' => array_sum(array_column($items, 'full')),
            'half' => array_sum(array_column($items, 'half')),
        ];

        return compact('items', 'totals');
    }

    /**
     * Display Rekap SPP
     */
    public function rekapSpp(Request $request)
    {
        if (!$this->jenjangId) abort(404, 'Jenjang SD tidak ditemukan');

        $selectedBulan = $request->get('bulan', $this->getDefaultBulan());

        $data   = $this->buildRekapSppData($selectedBulan);
        $rows   = $data['rows'];
        $totals = $data['totals'];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'rows' => $rows, 'totals' => $totals]);
        }

        $bulanList = $this->bulanList;
        return view('admin.sd.partials.rekap_spp', compact('bulanList', 'selectedBulan', 'rows', 'totals'));
    }

    private function buildRekapSppData(?string $selectedBulan = null): array
    {
        $targetBulan = $selectedBulan ? (self::BULAN_NOMOR[$selectedBulan] ?? null) : null;
        $rows = [];

        foreach ($this->kelasList as $kelas) {
            $golonganId = $this->mapKelasToGolongan(str_replace('Kelas ', '', $kelas));

            $getSppTotal = function ($catCode, $legacyName) use ($golonganId, $targetBulan) {
                $query = Pembayaran::whereHas('tagihan', function ($q) use ($golonganId, $catCode) {
                    $q->where('jenis', 'spp')
                      ->whereHas('siswa', function ($sq) use ($golonganId, $catCode) {
                          $sq->where('golongan_id', $golonganId);
                          if ($catCode === 'APK') {
                              $sq->where(function ($w) {
                                  $w->whereIn('golongan_kategori', ['APK', 'Anak Pegawai Khusus'])
                                    ->orWhere('golongan_asal', 'APK');
                              });
                          } elseif ($catCode === 'ANP') {
                              $sq->where(function ($w) {
                                  $w->whereIn('golongan_kategori', ['ANP', 'Anak Pegawai'])
                                    ->orWhere('golongan_asal', 'ANP');
                              });
                          } else {
                              // REGULER: siswa yang bukan ANP dan bukan APK
                              $sq->where(function ($w) {
                                  $w->whereNotIn('golongan_kategori', ['ANP', 'Anak Pegawai', 'APK', 'Anak Pegawai Khusus'])
                                    ->orWhereNull('golongan_kategori')
                                    ->orWhere('golongan_kategori', '');
                              })->where(function ($w) {
                                  $w->whereNotIn('golongan_asal', ['ANP', 'APK'])
                                    ->orWhereNull('golongan_asal');
                              });
                          }
                      });
                });
                // Filter berdasarkan bulan transaksi pembayaran (tanggal_bayar),
                // bukan bulan tagihan — agar pembayaran multi-bulan sekaligus
                // terhitung penuh pada bulan terjadinya transaksi.
                if ($targetBulan) {
                    $query->whereMonth('tanggal_bayar', $targetBulan);
                }
                return (float) $query->sum('jumlah_bayar');
            };

            $rows[] = [
                'kelas' => $kelas,
                'reg'   => $getSppTotal('REG', 'Reguler'),
                'anp'   => $getSppTotal('ANP', 'Anak Pegawai'),
                'apk'   => $getSppTotal('APK', 'Anak Pegawai Khusus'),
            ];
        }

        $totals = [
            'reg' => array_sum(array_column($rows, 'reg')),
            'anp' => array_sum(array_column($rows, 'anp')),
            'apk' => array_sum(array_column($rows, 'apk')),
        ];

        return compact('rows', 'totals');
    }

    /**
     * Calculate student UAP nominal obligation
     */
    private function calculateStudentUapNominal(Siswa $siswa, string $kelas): float
    {
        $golonganKey  = $siswa->golongan_kategori ?? 'REG';
        $kategoriName = self::SHORTHAND_KATEGORI[$golonganKey] ?? 'Reguler';

        $settingNominal = (float) SettingItemUangPangkal::where('jenjang_id', $this->jenjangId)
            ->where('kelas', $kelas)
            ->whereIn('kategori', [$golonganKey, $kategoriName])
            ->where('is_aktif', true)
            ->sum('nominal');

        $studentItemNominal = (float) $siswa->itemUangPangkals->sum('nominal');
        $tagihan = $siswa->tagihans->firstWhere('jenis', 'uang_pangkal');

        return max($settingNominal, $studentItemNominal, $tagihan ? (float) $tagihan->nominal : 0);
    }

    /**
     * Get UAP (Uang Pangkal) data for a specific kelas
     */
    public function uapKelas(Request $request)
    {
        if (!$this->jenjangId) abort(404, 'Jenjang SD tidak ditemukan');

        $kelasFilter = $request->get('kelas', '1');
        $golonganId  = $this->mapKelasToGolongan($kelasFilter);
        $kelas       = 'Kelas ' . $kelasFilter;

        $siswas = Siswa::byJenjang($this->jenjangId)
            ->byGolongan($golonganId)
            ->with(['itemUangPangkals', 'tagihans' => fn($q) => $q->where('jenis', 'uang_pangkal')->with('pembayarans')])
            ->get();

        $bulanOrder = ['Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];

        $rows = $siswas->map(function ($siswa) use ($kelas, $bulanOrder) {
            $golonganKey = $siswa->golongan_kategori ?? 'REG';
            $nominal     = $this->calculateStudentUapNominal($siswa, $kelas);

            $tagihan = $siswa->tagihans->firstWhere('jenis', 'uang_pangkal');
            $trxMonths = [];
            $monthlyPayments = [];

            // Hitung terbayar dari jumlah pembayaran aktual (bukan persentase)
            // agar sisa yang ditampilkan konsisten dan tidak ada selisih floating-point
            $terbayar = 0;
            if ($tagihan) {
                foreach ($tagihan->pembayarans as $p) {
                    $terbayar += (float) $p->jumlah_bayar;
                    $m = Carbon::parse($p->tanggal_bayar ?? $p->created_at)->month;
                    $abbr = self::BULAN_ABBR[$m] ?? null;
                    if ($abbr) {
                        if (!in_array($abbr, $trxMonths)) {
                            $trxMonths[] = $abbr;
                        }
                        $monthlyPayments[$abbr] = ($monthlyPayments[$abbr] ?? 0) + (float) $p->jumlah_bayar;
                    }
                }
            }

            $terbayar = round($terbayar, 2);

            $isNonAktif = in_array(strtolower($siswa->status_siswa), ['non_aktif', 'pindah']);
            // Bulatkan sisa ke satuan rupiah agar tidak ada tampilan "Rp 2" atau "Rp 0.01"
            $sisa   = $isNonAktif ? 0 : max(0, (int) round($nominal - $terbayar));
            $status = $isNonAktif ? 'Non Aktif' : (($tagihan?->status === 'lunas' || $sisa <= 0 || ($nominal > 0 && $terbayar >= $nominal - 1)) ? 'Lunas' : 'Tunggak');

            // Hitung histori kumulatif UAP per bulan kalender akademik (Jul -> Jun)
            $cumPaid = 0;
            $monthlyHistory = [];
            foreach ($bulanOrder as $b) {
                $paidInB = (float) ($monthlyPayments[$b] ?? 0);
                $cumPaid += $paidInB;
                $sisaB = $isNonAktif ? 0 : max(0, (int) round($nominal - $cumPaid));
                $statusB = $isNonAktif ? 'Non Aktif' : (($sisaB <= 0 && $nominal > 0) ? 'Lunas' : 'Tunggak');

                $monthlyHistory[$b] = [
                    'has_trx'       => $paidInB > 0,
                    'nominal_bulan' => $paidInB,
                    'cum_paid'      => $cumPaid,
                    'sisa'          => $sisaB,
                    'status'        => $statusB,
                ];
            }

            return [
                'id'                => $siswa->id,
                'nama'              => $siswa->nama_siswa,
                'nisn'              => $siswa->nisn,
                'nominal'           => $nominal,
                'terbayar'          => $terbayar,
                'sisa'              => $sisa,
                'golongan'          => $golonganKey,
                'status'            => $status,
                'trx_months'        => $trxMonths,
                'monthly_payments'  => $monthlyPayments,
                'monthly_history'   => $monthlyHistory,
                'last_payment_date' => ($tagihan && $tagihan->pembayarans->isNotEmpty()) ? Carbon::parse($tagihan->pembayarans->last()->tanggal_bayar ?? $tagihan->pembayarans->last()->created_at)->format('Y-m-d') : null,
            ];
        })->values();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'rows' => $rows, 'kelas' => $kelas]);
        }

        $bulanList     = $this->bulanList;
        $selectedBulan = $request->get('bulan', $this->getDefaultBulan());

        return view('admin.sd.kelas.uap', [
            'kelas'         => $kelas,
            'kelasFilter'   => $kelasFilter,
            'transaksi'     => $rows,
            'bulanList'     => $bulanList,
            'selectedBulan' => $selectedBulan,
        ]);
    }

    /**
     * Store UAP payment
     */
    public function storeUapKelas(Request $request)
    {
        $request->validate([
            'siswa_id'           => 'required|exists:siswas,id',
            'nominal_pembayaran' => 'required|numeric|min:1',
            'tanggal_pembayaran' => 'nullable|date',
        ]);

        $result = $this->pembayaranService->bayarUangPangkal(
            siswaId: $request->siswa_id,
            jumlah:  $request->nominal_pembayaran,
            tanggal: $request->tanggal_pembayaran,
        );

        return response()->json(
            ['success' => $result['success'], 'message' => $result['message']],
            $result['success'] ? 200 : 422
        );
    }

    /**
     * Koreksi sisa UAP: set target nominal pembayaran atau sisa (bisa naik atau turun).
     * - Jika nominal_pembayaran naik → tambah pembayaran selisih
     * - Jika nominal_pembayaran turun → kurangi/hapus record pembayaran
     */
    public function koreksiSisaUAP(Request $request)
    {
        $request->validate([
            'siswa_id'           => 'required|exists:siswas,id',
            'nominal_pembayaran' => 'nullable|numeric|min:0',
            'sisa_target'        => 'nullable|numeric|min:0',
            'tanggal'            => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $siswa = Siswa::with([
                'itemUangPangkals',
                'tagihans' => fn($q) => $q->where('jenis', 'uang_pangkal')->with('pembayarans'),
            ])->findOrFail($request->siswa_id);

            $tagihan = $siswa->tagihans->firstWhere('jenis', 'uang_pangkal');
            if (!$tagihan) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Tagihan UAP tidak ditemukan.'], 422);
            }

            $items           = $siswa->itemUangPangkals;
            $totalNominal    = (float) $items->sum('nominal');
            $currentTerbayar = (float) Pembayaran::where('tagihan_id', $tagihan->id)->sum('jumlah_bayar');
            $currentSisa     = max(0, $totalNominal - $currentTerbayar);

            if ($request->has('nominal_pembayaran') && $request->nominal_pembayaran !== null && $request->nominal_pembayaran !== '') {
                $targetTerbayar = (float) $request->nominal_pembayaran;
                if ($targetTerbayar > $totalNominal) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Nominal pembayaran tidak boleh melebihi total Uang Pangkal (Rp ' . number_format($totalNominal, 0, ',', '.') . ').',
                    ], 422);
                }
                $sisaTarget = max(0, $totalNominal - $targetTerbayar);
            } else {
                $sisaTarget = (float) $request->sisa_target;
                if ($sisaTarget > $totalNominal) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Sisa target tidak boleh melebihi total Uang Pangkal (Rp ' . number_format($totalNominal, 0, ',', '.') . ').',
                    ], 422);
                }
                $targetTerbayar = max(0, $totalNominal - $sisaTarget);
            }

            $diff = $targetTerbayar - $currentTerbayar; // positif = tambah bayar, negatif = kurangi bayar
            $tanggalBayar = $request->tanggal ? Carbon::parse($request->tanggal) : now();

            if (abs($diff) < 1) {
                // Cek apakah tanggal berubah
                $latestP = Pembayaran::where('tagihan_id', $tagihan->id)->latest('id')->first();
                if ($latestP && $request->tanggal && Carbon::parse($latestP->tanggal_bayar)->format('Y-m-d') !== Carbon::parse($request->tanggal)->format('Y-m-d')) {
                    $latestP->update(['tanggal_bayar' => $tanggalBayar]);

                    RiwayatTransaksi::create([
                        'siswa_id'          => $siswa->id,
                        'tanggal_transaksi' => $tanggalBayar->copy()->setTimeFrom(now()),
                        'function'          => 'Modifikasi',
                        'jenis'             => 'UAP',
                        'nominal'           => $currentTerbayar,
                        'sisa'              => $sisaTarget,
                        'nilai_akhir'       => $targetTerbayar,
                        'created_by'        => auth()->id(),
                    ]);

                    DB::commit();
                    return response()->json(['success' => true, 'message' => 'Tanggal pembayaran UAP berhasil diperbarui.']);
                }

                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Tidak ada perubahan pada data pembayaran.'], 422);
            }

            if ($diff > 0) {
                // Sisa turun / pembayaran naik → tambah record pembayaran selisih
                Pembayaran::create([
                    'tagihan_id'           => $tagihan->id,
                    'item_uang_pangkal_id' => $items->first()?->id,
                    'tanggal_bayar'        => $tanggalBayar,
                    'jumlah_bayar'         => round($diff),
                    'created_by'           => auth()->id() ?? 1,
                ]);
            } else {
                // Pembayaran turun → kurangi catatan pembayaran (koreksi overpayment)
                $toReduce = round(abs($diff));

                $pembayarans = Pembayaran::where('tagihan_id', $tagihan->id)
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->get();

                $remaining = $toReduce;
                foreach ($pembayarans as $p) {
                    if ($remaining <= 0) break;

                    $pAmount = (float) $p->jumlah_bayar;
                    if ($pAmount <= $remaining) {
                        $remaining -= $pAmount;
                        $p->delete();
                    } else {
                        $p->jumlah_bayar = $pAmount - $remaining;
                        $p->save();
                        $remaining = 0;
                    }
                }
            }

            // Update tanggal pembayaran pada record pembayaran terakhir jika disediakan tanggal baru
            if ($request->tanggal) {
                $latestP = Pembayaran::where('tagihan_id', $tagihan->id)->latest('id')->first();
                if ($latestP) {
                    $latestP->update(['tanggal_bayar' => $tanggalBayar]);
                }
            }

            // Recalculate persentase_dibayar per item berdasarkan pembayaran tersisa
            $newTerbayar = (float) Pembayaran::where('tagihan_id', $tagihan->id)->sum('jumlah_bayar');
            $newSisa     = max(0, $totalNominal - $newTerbayar);

            // Distribusikan terbayar baru ke item-item secara berurutan
            $toDistribute = $newTerbayar;
            foreach ($items as $item) {
                $itemNominal = (float) $item->nominal;
                if ($toDistribute >= $itemNominal) {
                    $item->persentase_dibayar = 100;
                    $item->status = 'lunas';
                    $toDistribute -= $itemNominal;
                } elseif ($toDistribute > 0) {
                    $item->persentase_dibayar = round(($toDistribute / $itemNominal) * 100, 4);
                    $item->status = 'belum_lunas';
                    $toDistribute = 0;
                } else {
                    $item->persentase_dibayar = 0;
                    $item->status = 'belum_lunas';
                }
                $item->save();
            }

            // Update status tagihan
            $allLunas = $items->every(fn($i) => $i->status === 'lunas');
            $tagihan->update(['status' => $allLunas ? 'lunas' : 'belum_lunas']);

            RiwayatTransaksi::create([
                'siswa_id'          => $siswa->id,
                'tanggal_transaksi' => $tanggalBayar->copy()->setTimeFrom(now()),
                'function'          => 'Modifikasi',
                'jenis'             => 'UAP',
                'nominal'           => $currentTerbayar,
                'sisa'              => $sisaTarget,
                'nilai_akhir'       => $targetTerbayar,
                'created_by'        => auth()->id(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Perubahan pembayaran UAP berhasil disimpan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper to calculate student SPP billable months, remaining months, nominal, and status
     */
    private function calculateStudentSppInfo(Siswa $siswa, ?int $tahunAjaranId, string $kelas): array
    {
        $now = Carbon::now();
        $tahunAjaran = Tahun_ajaran::getAktif();
        
        $startYear = $now->year;
        $endYear = $startYear + 1;
        if ($now->month < 7) {
            $startYear = $now->year - 1;
            $endYear = $now->year;
        }

        if ($tahunAjaran) {
            $parts = explode('/', $tahunAjaran->tahun_ajaran);
            if (count($parts) === 2) {
                $tStart = (int)$parts[0];
                $tEnd = (int)$parts[1];
                if ($tEnd >= $now->year) {
                    $startYear = $tStart;
                    $endYear = $tEnd;
                }
            }
        }

        $golonganKey  = $siswa->golongan_kategori ?? 'REG';
        $golonganAsal = $siswa->golongan_asal ?? (($golonganKey === 'ANP') ? 'ANP' : (($golonganKey === 'APK') ? 'APK' : 'REG'));
        // Golongan APK mengikuti tarif SPP dari golongan REG
        $baseSppCat   = ($golonganAsal === 'ANP') ? 'ANP' : 'REG';
        $kategoriName = self::SHORTHAND_KATEGORI[$baseSppCat] ?? 'Reguler';

        $setting = SettingSpp::where('jenjang_id', $this->jenjangId)
            ->where('kelas', $kelas)
            ->whereIn('kategori', [$baseSppCat, $kategoriName])
            ->first();

        $nominalSpp = $setting ? (float) $setting->nominal : (float) ($siswa->golongan->nominal_uang_spp ?? 0);

        $billableMonths = [];
        $bulanNomorList = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

        if ($siswa->tanggal_masuk) {
            $tanggalMasuk = Carbon::parse($siswa->tanggal_masuk);
            $tStartYear = ($tanggalMasuk->month >= 7) ? $tanggalMasuk->year : $tanggalMasuk->year - 1;
            $tEndYear   = $tStartYear + 1;

            if ($startYear == $tStartYear || in_array($golonganKey, ['HALF', 'FULL'])) {
                foreach ($bulanNomorList as $m) {
                    $y = ($m >= 7) ? $tStartYear : $tEndYear;
                    if ((($y * 12) + $m) >= (($tanggalMasuk->year * 12) + $tanggalMasuk->month)) {
                        $billableMonths[] = $m;
                    }
                }
            } else {
                $billableMonths = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
            }
        } else {
            $billableMonths = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
        }

        if ($golonganKey === 'HALF') {
            $billableMonths = array_values(array_filter($billableMonths, fn($m) => in_array($m, [1, 2, 3, 4, 5, 6])));
            if (empty($billableMonths)) $billableMonths = [1, 2, 3, 4, 5, 6];
        }

        if ($golonganKey === 'FULL' && empty($billableMonths)) {
            $billableMonths = [8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
        }

        if (empty($billableMonths)) {
            $billableMonths = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
        }

        $totalBulan = count($billableMonths);

        $paidTagihans = $siswa->tagihans
            ->where('jenis', 'spp')
            ->filter(function ($t) use ($tahunAjaranId) {
                if ($t->status !== 'lunas') return false;
                if (!$tahunAjaranId || !$t->tahun_ajaran_id) return true;
                return (int)$t->tahun_ajaran_id === (int)$tahunAjaranId;
            });

        $paidMonths = $paidTagihans
            ->pluck('bulan')
            ->map(fn($b) => (int)$b)
            ->unique()
            ->values()
            ->toArray();

        $lunasCount = count(array_intersect($billableMonths, $paidMonths));

        if (in_array(strtolower($siswa->status_siswa ?? ''), ['non_aktif', 'non aktif', 'pindah', 'out'])) {
            return [
                'golongan'        => $golonganKey,
                'nominal'         => $nominalSpp,
                'sisa'            => 0,
                'sisa_bulan'      => '0/0',
                'status'          => 'Non Aktif',
                'billable_months' => [],
                'paid_months'     => $paidMonths,
                'total_bulan'     => 0,
                'lunas_count'     => 0,
            ];
        }

        $sisaBulan = max(0, $totalBulan - $lunasCount);
        // Jika nominal SPP > 0 dan seluruh bulan lunas, status Lunas. Jika nominal <= 0 (belum diatur admin), tampilkan Tunggak sesuai instruksi user
        $status    = ($nominalSpp > 0 && $totalBulan > 0 && $lunasCount >= $totalBulan) ? 'Lunas' : 'Tunggak';

        return [
            'golongan'        => $golonganKey,
            'nominal'         => $nominalSpp,
            'sisa'            => $sisaBulan * $nominalSpp,
            'sisa_bulan'      => $lunasCount . '/' . $totalBulan,
            'status'          => $status,
            'billable_months' => $billableMonths,
            'paid_months'     => $paidMonths,
            'total_bulan'     => $totalBulan,
            'lunas_count'     => $lunasCount,
        ];
    }

    /**
     * SPP data per kelas
     */
    public function sppKelas(Request $request)
    {
        if (!$this->jenjangId) abort(404);

        $kelasFilter   = $request->get('kelas', '1');
        $golonganId    = $this->mapKelasToGolongan($kelasFilter);
        $kelas         = 'Kelas ' . $kelasFilter;
        $tahunAjaran   = Tahun_ajaran::getAktif();
        $tahunAjaranId = $tahunAjaran?->id;

        $siswas = Siswa::byJenjang($this->jenjangId)->byGolongan($golonganId)
            ->with(['golongan', 'tagihans' => fn($q) => $q->where('jenis', 'spp')->with('pembayarans')])
            ->get();

        $bulanOrder = $this->bulanList;
        $monthNumToIndex = [
            7 => 0, 8 => 1, 9 => 2, 10 => 3, 11 => 4, 12 => 5,
            1 => 6, 2 => 7, 3 => 8, 4 => 9, 5 => 10, 6 => 11
        ];

        $rows = $siswas->map(function ($siswa) use ($kelas, $tahunAjaranId, $bulanOrder, $monthNumToIndex) {
            $info = $this->calculateStudentSppInfo($siswa, $tahunAjaranId, $kelas);

            $trxMonths = [];
            $paidEvents = [];

            foreach ($siswa->tagihans as $tagihan) {
                if ($tahunAjaranId && $tagihan->tahun_ajaran_id && (int)$tagihan->tahun_ajaran_id !== (int)$tahunAjaranId) {
                    continue;
                }
                if ($tagihan->status !== 'lunas') {
                    continue;
                }

                $sppMonth = (int) $tagihan->bulan;

                if ($tagihan->pembayarans->isNotEmpty()) {
                    foreach ($tagihan->pembayarans as $p) {
                        $m = Carbon::parse($p->tanggal_bayar ?? $p->created_at)->month;
                        $abbr = self::BULAN_ABBR[$m] ?? null;
                        if ($abbr && !in_array($abbr, $trxMonths)) {
                            $trxMonths[] = $abbr;
                        }
                        $aIdx = $monthNumToIndex[$m] ?? 0;
                        $paidEvents[] = [
                            'spp_month'      => $sppMonth,
                            'academic_index' => $aIdx,
                            'abbr'           => $abbr,
                            'amount'         => (float) $p->jumlah_bayar,
                        ];
                    }
                } else {
                    $m = Carbon::parse($tagihan->created_at ?? now())->month;
                    $abbr = self::BULAN_ABBR[$m] ?? null;
                    if ($abbr && !in_array($abbr, $trxMonths)) {
                        $trxMonths[] = $abbr;
                    }
                    $aIdx = $monthNumToIndex[$m] ?? 0;
                    $paidEvents[] = [
                        'spp_month'      => $sppMonth,
                        'academic_index' => $aIdx,
                        'abbr'           => $abbr,
                        'amount'         => (float) $tagihan->nominal,
                    ];
                }
            }

            // Hitung histori SPP kumulatif per bulan kalender akademik (Jul -> Jun)
            $nominalSpp     = $info['nominal'];
            $billableMonths = $info['billable_months'];
            $totalBulan     = $info['total_bulan'];
            $isNonAktif     = in_array(strtolower($siswa->status_siswa), ['non_aktif', 'pindah']);

            $monthlyHistory = [];
            foreach ($bulanOrder as $bIdx => $b) {
                $paidMonthsUpToB = [];
                $hasTrxB = in_array($b, $trxMonths);

                foreach ($paidEvents as $ev) {
                    if ($ev['academic_index'] <= $bIdx) {
                        if (!in_array($ev['spp_month'], $paidMonthsUpToB)) {
                            $paidMonthsUpToB[] = $ev['spp_month'];
                        }
                    }
                }

                $lunasCountB = count($paidMonthsUpToB);
                $totalTerbayarB = $lunasCountB * $nominalSpp;

                if ($isNonAktif) {
                    $sisaB         = 0;
                    $sisaBulanStrB = '0/0';
                    $statusB       = 'Non Aktif';
                } else {
                    $sisaBulanCountB = max(0, $totalBulan - $lunasCountB);
                    $sisaB           = $sisaBulanCountB * $nominalSpp;
                    $sisaBulanStrB   = $lunasCountB . '/' . $totalBulan;
                    $statusB         = ($totalBulan > 0 && $lunasCountB >= $totalBulan) ? 'Lunas' : 'Tunggak';
                }

                $monthlyHistory[$b] = [
                    'has_trx'        => $hasTrxB,
                    'total_terbayar' => $totalTerbayarB,
                    'sisa'           => $sisaB,
                    'sisa_bulan'     => $sisaBulanStrB,
                    'status'         => $statusB,
                    'lunas_count'    => $lunasCountB,
                ];
            }

            return [
                'id'              => $siswa->id,
                'nama'            => $siswa->nama_siswa,
                'nisn'            => $siswa->nisn,
                'golongan'        => $info['golongan'],
                'nominal'         => $info['nominal'],
                'sisa'            => $info['sisa'],
                'sisa_bulan'      => $info['sisa_bulan'],
                'status'          => $info['status'],
                'billable_months' => $info['billable_months'],
                'paid_months'     => $info['paid_months'],
                'total_bulan'     => $info['total_bulan'],
                'lunas_count'     => $info['lunas_count'],
                'trx_months'      => $trxMonths,
                'monthly_history' => $monthlyHistory,
            ];
        })->values();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'rows' => $rows, 'kelas' => $kelas]);
        }

        $bulanList     = $this->bulanList;
        $selectedBulan = $request->get('bulan', $this->getDefaultBulan());
        $bulanLengkap  = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];

        return view('admin.sd.kelas.spp', compact(
            'kelas','kelasFilter','rows','bulanList','selectedBulan','bulanLengkap','tahunAjaranId'
        ) + ['transaksi' => $rows]);
    }

    /**
     * Store SPP payment
     */
    public function storeSppKelas(Request $request)
    {
        $request->validate([
            'siswa_id'        => 'required|exists:siswas,id',
            'bulan'           => 'required|array|min:1',
            'bulan.*'         => 'required|integer|between:1,12',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        $tahunAjaranId = $request->tahun_ajaran_id ?: (Tahun_ajaran::getAktif()?->id);

        $result = $this->pembayaranService->bayarSPP(
            siswaId:       $request->siswa_id,
            bulanArray:    $request->bulan,
            tahun:         Carbon::now()->year,
            tahunAjaranId: $tahunAjaranId,
        );

        return response()->json(
            ['success' => $result['success'], 'message' => $result['message']],
            $result['success'] ? 200 : 422
        );
    }

    /**
     * Koreksi SPP: sesuaikan bulan-bulan yang terbayar untuk seorang siswa (bisa un-ceklis atau ceklis bulan baru)
     */
    public function koreksiSppKelas(Request $request)
    {
        $request->validate([
            'siswa_id'        => 'required|exists:siswas,id',
            'bulan'           => 'nullable|array',
            'bulan.*'         => 'integer|between:1,12',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
        ]);

        DB::beginTransaction();
        try {
            $siswa = Siswa::with('golongan')->findOrFail($request->siswa_id);
            if (in_array(strtolower($siswa->status_siswa ?? ''), ['non_aktif', 'non aktif', 'pindah'])) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Siswa berstatus Non-Aktif tidak dapat diubah.'], 422);
            }

            $tahunAjaranId = $request->tahun_ajaran_id ?: (Tahun_ajaran::getAktif()?->id);
            $targetBulan   = array_map('intval', $request->bulan ?? []);

            // Ambil tagihan SPP siswa yang lunas
            $currentPaidTagihans = Tagihan::where('siswa_id', $siswa->id)
                ->where('jenis', 'spp')
                ->where('status', 'lunas')
                ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
                ->get();

            $currentPaidMonths = $currentPaidTagihans->pluck('bulan')->map(fn($b) => (int)$b)->toArray();

            // Bulan yang perlu dibatalkan (sebelumnya ada di paid, sekarang un-ceklis)
            $toCancel = array_diff($currentPaidMonths, $targetBulan);

            // Bulan yang perlu ditambah (sebelumnya belum paid, sekarang dicentang)
            $toAdd = array_diff($targetBulan, $currentPaidMonths);

            // 1. Hapus/batalkan bulan yang un-ceklis
            if (!empty($toCancel)) {
                $tagihansToCancel = $currentPaidTagihans->whereIn('bulan', $toCancel);
                foreach ($tagihansToCancel as $tagihan) {
                    $tagihan->pembayarans()->delete();
                    $tagihan->update(['status' => 'belum_lunas']);
                }
            }

            // 2. Tambah bulan yang baru dicentang
            if (!empty($toAdd)) {
                $result = $this->pembayaranService->bayarSPP(
                    siswaId:       $siswa->id,
                    bulanArray:    array_values($toAdd),
                    tahun:         Carbon::now()->year,
                    tahunAjaranId: $tahunAjaranId,
                    recordRiwayat: false,
                );

                if (!$result['success']) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => $result['message']], 422);
                }
            }

            if (count($currentPaidMonths) !== count($targetBulan)) {
                $nominalSpp = (float) ($currentPaidTagihans->first()?->nominal ?? ($this->calculateStudentSppInfo($siswa, $tahunAjaranId, 'Kelas ' . ($siswa->golongan?->nama_golongan ?? '1'))['nominal'] ?? 0));
                $oldTerbayar = count($currentPaidMonths) * $nominalSpp;
                $newTerbayar = count($targetBulan) * $nominalSpp;

                RiwayatTransaksi::create([
                    'siswa_id'          => $siswa->id,
                    'tanggal_transaksi' => now(),
                    'function'          => 'Modifikasi',
                    'jenis'             => 'SPP',
                    'nominal'           => $oldTerbayar,
                    'sisa'              => null,
                    'nilai_akhir'       => $newTerbayar,
                    'created_by'        => auth()->id(),
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Perubahan SPP berhasil disimpan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Kelola Siswa per kelas
     */
    public function kelolaSiswaKelas(Request $request)
    {
        if (!$this->jenjangId) abort(404);

        $kelasFilter   = $request->get('kelas', '1');
        $golonganId    = $this->mapKelasToGolongan($kelasFilter);
        $kelas         = 'Kelas ' . $kelasFilter;
        $tahunAjaran   = Tahun_ajaran::getAktif();
        $tahunAjaranId = $tahunAjaran?->id;

        $siswas = Siswa::byJenjang($this->jenjangId)->byGolongan($golonganId)
            ->with(['golongan', 'itemUangPangkals', 'riwayatTransaksis', 'tagihans' => fn($q) => $q->with('pembayarans')])->get();

        $rows = $siswas->map(function ($siswa) use ($tahunAjaranId, $kelas) {
            $info = $this->calculateStudentSppInfo($siswa, $tahunAjaranId, $kelas);
            $nominalUap = $this->calculateStudentUapNominal($siswa, $kelas);

            $uapTagihan  = $siswa->tagihans->where('jenis', 'uang_pangkal')->first();
            $isNonAktif  = in_array(strtolower($siswa->status_siswa ?? ''), ['non_aktif', 'non aktif', 'pindah', 'out']);
            $terbayarUap = $uapTagihan ? (float) $uapTagihan->pembayarans->sum('jumlah_bayar') : 0;

            if ($isNonAktif) {
                $uapStatus = 'Non Aktif';
            } elseif ($uapTagihan?->status === 'lunas' || ($nominalUap > 0 && $terbayarUap >= $nominalUap - 0.99) || ($uapTagihan && (float)$uapTagihan->nominal > 0 && $terbayarUap >= (float)$uapTagihan->nominal - 0.99)) {
                $uapStatus = 'Lunas';
            } else {
                // Jika nominalUap <= 0 atau belum lunas, badge menunjukkan 'Tunggak' sesuai instruksi user
                $uapStatus = 'Tunggak';
            }

            $riwayatTransaksis = $siswa->riwayatTransaksis->sortByDesc('tanggal_transaksi')->values();
            $riwayat = [];

            // Selalu tampilkan dari riwayat_transaksis terlebih dahulu
            if ($riwayatTransaksis->isNotEmpty()) {
                foreach ($riwayatTransaksis as $rt) {
                    $tgl = Carbon::parse($rt->tanggal_transaksi ?? $rt->created_at);
                    $riwayat[] = [
                        'tanggal'     => $tgl->translatedFormat('d F Y'),
                        'jam'         => $tgl->format('H.i'),
                        'function'    => $rt->function ?: 'Bayar',
                        'jenis'       => $rt->jenis ?: 'SPP',
                        'nominal'     => (float) $rt->nominal,
                        'sisa'        => $rt->sisa !== null ? (float) $rt->sisa : null,
                        'menjadi'     => ($rt->function === 'Modifikasi' || $rt->nilai_akhir !== null) ? '>' : null,
                        'nilai_akhir' => $rt->nilai_akhir !== null ? (float) $rt->nilai_akhir : null,
                        'sort_ts'     => $tgl->timestamp,
                    ];
                }
            }

            // Fallback hybrid: jika belum ada entri UAP di riwayat_transaksis
            // (data lama sebelum bug fix amountPaid), ambil dari tabel pembayarans UAP.
            $hasUapInRiwayat = $riwayatTransaksis->contains(fn($rt) => strtoupper($rt->jenis ?? '') === 'UAP');
            if (!$hasUapInRiwayat) {
                $uapTagihanFallback = $siswa->tagihans->where('jenis', 'uang_pangkal')->first();
                if ($uapTagihanFallback && $uapTagihanFallback->pembayarans->isNotEmpty()) {
                    $uapByDay = $uapTagihanFallback->pembayarans->groupBy(
                        fn($p) => Carbon::parse($p->tanggal_bayar ?? $p->created_at)->format('Y-m-d')
                    );
                    foreach ($uapByDay as $day => $group) {
                        $tgl = Carbon::parse($day);
                        $firstP = $group->first();
                        $riwayat[] = [
                            'tanggal'     => $tgl->translatedFormat('d F Y'),
                            'jam'         => Carbon::parse($firstP->tanggal_bayar ?? $firstP->created_at)->format('H.i'),
                            'function'    => 'Bayar',
                            'jenis'       => 'UAP',
                            'nominal'     => (float) $group->sum('jumlah_bayar'),
                            'sisa'        => null,
                            'menjadi'     => null,
                            'nilai_akhir' => null,
                            'sort_ts'     => $tgl->timestamp,
                        ];
                    }
                }
            }

            // Fallback hybrid: jika belum ada entri SPP di riwayat_transaksis,
            // ambil dari tabel pembayarans SPP.
            $hasSppInRiwayat = $riwayatTransaksis->contains(fn($rt) => strtoupper($rt->jenis ?? '') === 'SPP');
            if (!$hasSppInRiwayat) {
                $sppPayments = collect();
                foreach ($siswa->tagihans->where('jenis', 'spp') as $sppTagihan) {
                    foreach ($sppTagihan->pembayarans as $p) {
                        $tgl = Carbon::parse($p->tanggal_bayar ?? $p->created_at);
                        $sppPayments->push([
                            'jumlah_bayar' => (float) $p->jumlah_bayar,
                            'tgl'          => $tgl,
                            'date_day'     => $tgl->format('Y-m-d'),
                        ]);
                    }
                }
                foreach ($sppPayments->groupBy('date_day') as $group) {
                    $tgl = $group->first()['tgl'];
                    $riwayat[] = [
                        'tanggal'     => $tgl->translatedFormat('d F Y'),
                        'jam'         => $tgl->format('H.i'),
                        'function'    => 'Bayar',
                        'jenis'       => 'SPP',
                        'nominal'     => (float) $group->sum('jumlah_bayar'),
                        'sisa'        => null,
                        'menjadi'     => null,
                        'nilai_akhir' => null,
                        'sort_ts'     => $tgl->timestamp,
                    ];
                }
            }

            // Urutkan berdasarkan timestamp descending
            usort($riwayat, fn($a, $b) => ($b['sort_ts'] ?? 0) - ($a['sort_ts'] ?? 0));
            foreach ($riwayat as &$r) unset($r['sort_ts']);

            return [
                'id'                      => $siswa->id,
                'nama'                    => $siswa->nama_siswa,
                'nisn'                    => $siswa->nisn,
                'golongan'                => $info['golongan'],
                'golongan_asal'           => $siswa->golongan_asal ?? (in_array($info['golongan'], ['ANP', 'APK']) ? $info['golongan'] : 'REG'),
                'tanggal_masuk'           => $siswa->tanggal_masuk ? Carbon::parse($siswa->tanggal_masuk)->format('Y-m-d') : '',
                'tanggal_masuk_formatted' => $siswa->tanggal_masuk ? Carbon::parse($siswa->tanggal_masuk)->translatedFormat('d F Y') : '',
                'status_siswa'            => $isNonAktif ? 'Non Aktif' : 'Aktif',
                'nominal_uap'             => $nominalUap,
                'nominal_spp'             => $info['nominal'],
                'uap'                     => $uapStatus,
                'spp'                     => $info['status'],
                'riwayat'                 => $riwayat,
            ];
        })->values();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'rows' => $rows]);
        }

        $golonganOptions = ['REG', 'ANP', 'APK'];

        // Map nominal setting per kategori untuk preview real-time di UI
        $sppRates = [];
        $uapRates = [];
        foreach (['REG', 'ANP', 'APK', 'FULL', 'HALF'] as $cat) {
            $legacyCat     = self::SHORTHAND_KATEGORI[$cat] ?? 'Reguler';
            $baseSppCat    = ($cat === 'ANP') ? 'ANP' : 'REG';
            $baseSppLegacy = self::SHORTHAND_KATEGORI[$baseSppCat] ?? 'Reguler';

            $settingSpp = SettingSpp::where('jenjang_id', $this->jenjangId)
                ->where('kelas', $kelas)
                ->whereIn('kategori', [$baseSppCat, $baseSppLegacy])
                ->first();
            $sppRates[$cat] = $settingSpp ? (float)$settingSpp->nominal : 0;

            $settingUap = (float) SettingItemUangPangkal::where('jenjang_id', $this->jenjangId)
                ->where('kelas', $kelas)
                ->whereIn('kategori', [$cat, $legacyCat])
                ->where('is_aktif', true)
                ->sum('nominal');
            $uapRates[$cat] = $settingUap;
        }

        return view('admin.sd.kelas.kelolasiswa', compact('kelas','kelasFilter','golonganOptions','sppRates','uapRates') + ['siswa' => $rows]);
    }

    /**
     * Hitung golongan_kategori (HALF, FULL, atau golongan_asal) berdasarkan tanggal_masuk.
     */
    private function hitungGolonganKategori(?string $tanggalMasukStr, string $golonganAsal = 'REG'): string
    {
        if (!$tanggalMasukStr) {
            return $golonganAsal;
        }

        $tanggalMasuk = Carbon::parse($tanggalMasukStr);
        $tStartYear   = ($tanggalMasuk->month >= 7) ? $tanggalMasuk->year : $tanggalMasuk->year - 1;
        $tEndYear     = $tStartYear + 1;

        $billableMonths = [];
        $bulanNomorList = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];

        foreach ($bulanNomorList as $m) {
            $y = ($m >= 7) ? $tStartYear : $tEndYear;
            if ((($y * 12) + $m) >= (($tanggalMasuk->year * 12) + $tanggalMasuk->month)) {
                $billableMonths[] = $m;
            }
        }
        $totalBulan = count($billableMonths);

        if ($totalBulan <= 6) {
            return 'HALF';
        } elseif ($totalBulan < 12) {
            return 'FULL';
        }

        return $golonganAsal;
    }

    /**
     * Store new Siswa
     */
    public function storeSiswaKelas(Request $request)
    {
        $request->validate([
            'nama_siswa'    => 'required|string|max:255',
            'nisn'          => 'required|string|max:20|unique:siswas,nisn',
            'tanggal_masuk' => 'required|date',
            'kelas'         => 'required|string',
            'golongan'      => 'required|string|in:REG,ANP,APK',
        ]);

        $golonganId = $this->mapKelasToGolongan($request->kelas);
        if (!$golonganId) {
            return response()->json(['success' => false, 'message' => 'Kelas tidak valid.'], 422);
        }

        $golonganAsal     = $request->golongan;
        $golonganKategori = $this->hitungGolonganKategori($request->tanggal_masuk, $golonganAsal);

        $siswa = Siswa::create([
            'nama_siswa'        => $request->nama_siswa,
            'nisn'              => $request->nisn,
            'tanggal_masuk'     => $request->tanggal_masuk,
            'status_siswa'      => 'aktif',
            'golongan_id'       => $golonganId,
            'golongan_kategori' => $golonganKategori,
            'golongan_asal'     => $golonganAsal,
        ]);

        return response()->json(['success' => true, 'message' => 'Siswa berhasil ditambahkan.', 'siswa_id' => $siswa->id]);
    }

    /**
     * Update Siswa details
     */
    public function updateSiswaKelas(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);

        $request->validate([
            'nama'          => 'required|string|max:255',
            'nisn'          => 'required|string|max:20|unique:siswas,nisn,' . $siswa->id,
            'golongan'      => 'nullable|string',
            'golongan_asal' => 'nullable|string',
            'status_siswa'  => 'nullable|string',
            'tanggal_masuk' => 'nullable|date',
        ]);

        $updateData = ['nama_siswa' => $request->nama, 'nisn' => $request->nisn];
        if ($request->filled('status_siswa')) {
            $updateData['status_siswa'] = (strtolower($request->status_siswa) === 'aktif') ? 'aktif' : 'non_aktif';
        }

        $golonganInput = $request->input('golongan');
        $golonganAsal  = $request->input('golongan_asal');

        // Tentukan golongan asal
        if ($golonganAsal && in_array($golonganAsal, ['REG', 'ANP', 'APK'])) {
            $updateData['golongan_asal'] = $golonganAsal;
        } elseif ($golonganInput && in_array($golonganInput, ['REG', 'ANP', 'APK'])) {
            $updateData['golongan_asal'] = $golonganInput;
            $golonganAsal = $golonganInput;
        } else {
            $golonganAsal = $siswa->golongan_asal ?? (in_array($siswa->golongan_kategori, ['ANP', 'APK']) ? $siswa->golongan_kategori : 'REG');
        }

        if ($request->filled('tanggal_masuk')) {
            $updateData['tanggal_masuk'] = $request->tanggal_masuk;

            $oldTanggal = $siswa->tanggal_masuk ? Carbon::parse($siswa->tanggal_masuk)->format('Y-m-d') : null;
            $newTanggal = Carbon::parse($request->tanggal_masuk)->format('Y-m-d');

            $calculatedGolongan = $this->hitungGolonganKategori($request->tanggal_masuk, $golonganAsal);

            if ($oldTanggal !== $newTanggal) {
                // Tanggal masuk berubah: update golongan_kategori sesuai tanggal masuk yang baru
                $updateData['golongan_kategori'] = $calculatedGolongan;
            } else {
                // Tanggal masuk tidak berubah: gunakan golongan input jika ada, fallback ke calculated
                $updateData['golongan_kategori'] = $golonganInput ?: $calculatedGolongan;
            }
        } elseif ($golonganInput) {
            $updateData['golongan_kategori'] = $golonganInput;
        }

        $siswa->update($updateData);
        return response()->json(['success' => true, 'message' => 'Data siswa berhasil diperbarui!']);
    }

    /**
     * Delete Siswa
     */
    public function destroySiswaKelas($id)
    {
        $siswa = Siswa::findOrFail($id);
        $nama  = $siswa->nama_siswa;
        $siswa->delete();

        return response()->json(['success' => true, 'message' => "Siswa {$nama} berhasil dihapus!"]);
    }

    /**
     * Naik Kelas
     */
    public function naikKelas(Request $request)
    {
        $request->validate(['kelas' => 'required|string|in:1,2,3,4,5,6']);

        $kelasFilter = $request->kelas;
        $golonganId  = $this->mapKelasToGolongan($kelasFilter);
        if (!$golonganId) {
            return response()->json(['success' => false, 'message' => 'Kelas tidak valid.'], 422);
        }

        $kelas           = 'Kelas ' . $kelasFilter;
        $tahunAjaran     = Tahun_ajaran::getAktif();
        $tahunAjaranId   = $tahunAjaran?->id;
        $nextKelasFilter = (int)$kelasFilter + 1;
        $nextGolonganId  = $this->mapKelasToGolongan((string)$nextKelasFilter);

        // Cek apakah kelas berikutnya masih terdapat siswa aktif
        if ($nextGolonganId) {
            $siswaDiKelasAtas = Siswa::byJenjang($this->jenjangId)
                ->byGolongan($nextGolonganId)
                ->count();

            if ($siswaDiKelasAtas > 0) {
                $nextKelasLabel = 'Kelas ' . $nextKelasFilter;
                return response()->json([
                    'success' => false,
                    'message' => "Masih terdapat {$siswaDiKelasAtas} siswa di {$nextKelasLabel}. Silakan naik kelas siswa di {$nextKelasLabel} terlebih dahulu sebelum menaikkan kelas ini.",
                ], 422);
            }
        }

        $siswas = Siswa::byJenjang($this->jenjangId)->byGolongan($golonganId)
            ->with(['golongan', 'itemUangPangkals', 'tagihans' => fn($q) => $q->with('pembayarans')])
            ->get();

        $promoted = 0;
        $removed  = 0;

        foreach ($siswas as $siswa) {
            if (in_array(strtolower($siswa->status_siswa ?? ''), ['non_aktif', 'non aktif', 'pindah', 'out'])) {
                $siswa->delete();
                $removed++;
                continue;
            }

            $nominalUap  = $this->calculateStudentUapNominal($siswa, $kelas);
            $uapTagihan  = $siswa->tagihans->where('jenis', 'uang_pangkal')->first();
            $terbayarUap = $uapTagihan ? (float) $uapTagihan->pembayarans->sum('jumlah_bayar') : 0;

            // Jika nominal UAP di kelas asal belum diatur oleh admin (<= 0), siswa tetap dapat naik kelas
            if ($nominalUap <= 0) {
                $uapLunas = true;
            } else {
                $uapLunas = ($uapTagihan?->status === 'lunas')
                    || ($uapTagihan && (float)$uapTagihan->nominal > 0 && $terbayarUap >= (float)$uapTagihan->nominal - 0.99)
                    || (round($nominalUap - $terbayarUap, 2) <= 0)
                    || ($nominalUap > 0 && $terbayarUap >= $nominalUap - 0.99);
            }

            $sppInfo = $this->calculateStudentSppInfo($siswa, $tahunAjaranId, $kelas);

            // Jika nominal SPP di kelas asal belum diatur oleh admin (<= 0) atau tidak ada kewajiban bulan, siswa tetap dapat naik kelas
            if ($sppInfo['nominal'] <= 0 || $sppInfo['total_bulan'] <= 0) {
                $sppLunas = true;
            } else {
                $sppLunas = ($sppInfo['status'] === 'Lunas');
            }

            $isLunas = ($uapLunas && $sppLunas) || strtolower($siswa->status_siswa ?? '') === 'lunas';

            if ($isLunas) {
                if ($nextGolonganId) {
                    // Bersihkan tagihan lama dan mulai tagihan baru di kelas berikutnya
                    foreach ($siswa->tagihans as $tagihan) {
                        $tagihan->pembayarans()->delete();
                    }
                    $siswa->tagihans()->delete();
                    $siswa->itemUangPangkals()->delete();

                    $golonganAsalSiswa = $siswa->golongan_asal ?? 'REG';
                    $siswa->update([
                        'golongan_id'       => $nextGolonganId,
                        'status_siswa'      => 'aktif',
                        'golongan_kategori' => $golonganAsalSiswa,
                    ]);
                    $promoted++;
                } else {
                    // Kelas 6 Lulus
                    foreach ($siswa->tagihans as $tagihan) {
                        $tagihan->pembayarans()->delete();
                    }
                    $siswa->tagihans()->delete();
                    $siswa->itemUangPangkals()->delete();

                    $siswa->update([
                        'status_siswa' => 'lulus',
                    ]);
                    $promoted++;
                }
            } else {
                $siswa->delete();
                $removed++;
            }
        }

        $nextKelasLabel = $nextGolonganId ? ("Kelas " . $nextKelasFilter) : "Lulus / Alumnus";
        return response()->json([
            'success'    => true,
            'promoted'   => $promoted,
            'removed'    => $removed,
            'next_kelas' => ($nextKelasFilter <= 6 && $nextGolonganId) ? $nextKelasFilter : null,
            'message'    => "Proses naik kelas selesai. {$promoted} siswa dipromosikan ke {$nextKelasLabel}, {$removed} siswa dihapus.",
        ]);
    }

    /**
     * Cancel/Delete payment records
     */
    public function batalPembayaran(Request $request)
    {
        $request->validate([
            'pembayaran_ids'   => 'required|array',
            'pembayaran_ids.*' => 'integer|exists:pembayarans,id',
        ]);

        DB::beginTransaction();
        try {
            $pembayarans = Pembayaran::whereIn('id', $request->pembayaran_ids)->get();
            if ($pembayarans->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'Data pembayaran tidak ditemukan.']);
            }

            $tagihanIds = $pembayarans->pluck('tagihan_id')->unique();
            $itemUapIds = $pembayarans->pluck('item_uang_pangkal_id')->filter()->unique();

            Pembayaran::whereIn('id', $request->pembayaran_ids)->delete();

            foreach ($itemUapIds as $itemId) {
                $item = ItemUangPangkal::find($itemId);
                if ($item) {
                    $remSum = (float) Pembayaran::where('item_uang_pangkal_id', $item->id)->sum('jumlah_bayar');
                    $persentase = ($item->nominal > 0) ? min(100, ($remSum / (float) $item->nominal) * 100) : 0;
                    $item->update([
                        'persentase_dibayar' => round($persentase, 2),
                        'status'             => ($persentase >= 99.99) ? 'lunas' : 'belum_lunas',
                    ]);
                }
            }

            foreach ($tagihanIds as $tagihanId) {
                $tagihan = Tagihan::with('siswa')->find($tagihanId);
                if ($tagihan) {
                    $remTerbayar = (float) $tagihan->pembayarans()->sum('jumlah_bayar');
                    if ($tagihan->jenis === 'uang_pangkal') {
                        $allItemLunas = ItemUangPangkal::where('siswa_id', $tagihan->siswa_id)->where('status', '!=', 'lunas')->count() === 0;
                        $status = ($allItemLunas || ($remTerbayar >= ((float) $tagihan->nominal - 0.99) && (float) $tagihan->nominal > 0)) ? 'lunas' : 'belum_lunas';
                    } else {
                        $status = ($remTerbayar >= ((float) $tagihan->nominal - 0.99) && (float) $tagihan->nominal > 0) ? 'lunas' : 'belum_lunas';
                    }
                    $tagihan->update(['status' => $status]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pembayaran berhasil dibatalkan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal membatalkan pembayaran: ' . $e->getMessage()], 500);
        }
    }

    private function mapKelasToGolongan($kelas)
    {
        $cleanKelas = trim(str_replace('Kelas', '', (string)$kelas));

        $golongan = Golongan::where('jenjang_id', $this->jenjangId)
            ->where(function($q) use ($kelas, $cleanKelas) {
                $q->where('nama_golongan', 'Kelas ' . $cleanKelas)
                  ->orWhere('nama_golongan', $kelas);
            })
            ->first();
        return $golongan?->id;
    }

    private function mapGolonganToKelas($namaGolongan)
    {
        if (preg_match('/Kelas (\d+)/', $namaGolongan, $matches)) {
            return $matches[1];
        }
        return strtolower(str_replace(' ', '', $namaGolongan));
    }
}
