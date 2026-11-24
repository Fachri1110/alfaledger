<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\ItemUangPangkal;
use App\Models\Jenjang;
use App\Models\Golongan;
use App\Models\RiwayatTransaksi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

/**
 * PembayaranService
 * 
 * Handles all payment-related business logic
 */
class PembayaranService
{
    /**
     * Process Uang Pangkal payment (installment to specific item with percentage)
     * 
     * @param int $siswaId
     * @param float $jumlah Amount to pay
     * @param int|null $itemId Specific item ID, if null will distribute to all unpaid items
     * @return array Result with success status and message
     */
    public function bayarUangPangkal($siswaId, $jumlah, $itemId = null, $tanggal = null, $recordRiwayat = true)
    {
        DB::beginTransaction();
        try {
            $siswa = Siswa::with('golongan')->findOrFail($siswaId);
            if (in_array(strtolower($siswa->status_siswa ?? ''), ['non_aktif', 'non aktif', 'pindah'])) {
                return ['success' => false, 'message' => 'Siswa berstatus Non-Aktif tidak dapat melakukan transaksi pembayaran.'];
            }
            $tahunAjaran = \App\Models\Tahun_ajaran::getAktif();
            $tahunAjaranId = $tahunAjaran ? $tahunAjaran->id : null;
            
            $jenjangId = $siswa->golongan->jenjang_id ?? null;
            $kelasName = $siswa->golongan->nama_golongan ?? 'Kelas 1';
            
            $shorthandToKategori = [
                'REG'  => 'Reguler',
                'ANP'  => 'Anak Pegawai',
                'APK'  => 'Anak Pegawai Khusus',
                'FULL' => 'Pindahan Satu Tahun',
                'HALF' => 'Pindahan Setengah Tahun',
            ];
            $golonganKey = $siswa->golongan_kategori ?? 'REG';
            $kategoriName = $shorthandToKategori[$golonganKey] ?? 'Reguler';

            $defaultNames = [
                1 => 'Gedung',       2 => 'Materi',     3 => 'Praktek',
                4 => 'Kegiatan',     5 => 'Ekstrakurikuler', 6 => 'Komputer',
                7 => 'Administrasi', 8 => 'Raport',     9 => 'Badge',
            ];

            $settingItems = \App\Models\SettingItemUangPangkal::where('jenjang_id', $jenjangId)
                ->where('kelas', $kelasName)
                ->whereIn('kategori', [$golonganKey, $kategoriName])
                ->where('is_aktif', true)
                ->where('nominal', '>', 0)
                ->orderBy('item_index', 'asc')
                ->get();

            // Auto-initialize student's ItemUangPangkal if empty or if previously created as a single fallback item
            $existingCount = $siswa->itemUangPangkals()->count();
            $isSingleFallback = ($existingCount === 1 && $siswa->itemUangPangkals()->first()?->nama_item === 'Gedung');

            if ($existingCount === 0 || ($isSingleFallback && $settingItems->count() > 1)) {
                if ($isSingleFallback) {
                    $siswa->itemUangPangkals()->delete();
                }

                if ($settingItems->isNotEmpty()) {
                    foreach ($settingItems as $setting) {
                        ItemUangPangkal::create([
                            'siswa_id'        => $siswaId,
                            'tahun_ajaran_id' => $tahunAjaranId,
                            'nama_item'       => $setting->nama_item ?: ($defaultNames[$setting->item_index] ?? 'Item Uang Pangkal'),
                            'nominal'         => $setting->nominal,
                            'persentase_dibayar' => 0,
                            'status'          => 'belum_lunas',
                        ]);
                    }
                }
            }

            // Total nominal of Uang Pangkal for this student
            $totalNominalUangPangkal = (float) $siswa->itemUangPangkals()->sum('nominal');

            // Fallback if no items configured
            if ($totalNominalUangPangkal <= 0) {
                $totalNominalUangPangkal = (float) ($siswa->golongan->nominal_uang_pangkal ?? 0);
                if ($totalNominalUangPangkal <= 0) {
                    $totalNominalUangPangkal = 3500000;
                }
                ItemUangPangkal::create([
                    'siswa_id'        => $siswaId,
                    'tahun_ajaran_id' => $tahunAjaranId,
                    'nama_item'       => 'Gedung',
                    'nominal'         => $totalNominalUangPangkal,
                    'persentase_dibayar' => 0,
                    'status'          => 'belum_lunas',
                ]);
            }

            // Create or update Tagihan record with FULL total nominal
            $tagihan = Tagihan::firstOrCreate([
                'siswa_id' => $siswaId,
                'jenis'    => 'uang_pangkal',
            ], [
                'tahun_ajaran_id' => $tahunAjaranId,
                'bulan'           => null,
                'tahun'           => Carbon::now()->year,
                'nominal'         => $totalNominalUangPangkal,
                'status'          => 'belum_lunas',
            ]);

            if ((float)$tagihan->nominal !== $totalNominalUangPangkal && $totalNominalUangPangkal > 0) {
                $tagihan->update(['nominal' => $totalNominalUangPangkal]);
            }

            // Get items to pay
            if ($itemId) {
                $items = ItemUangPangkal::where('id', $itemId)
                    ->where('siswa_id', $siswaId)
                    ->belumLunas()
                    ->get();
            } else {
                $items = $siswa->itemUangPangkals()
                    ->belumLunas()
                    ->get();
            }

            if ($items->isEmpty()) {
                throw new Exception('Semua item uang pangkal sudah lunas');
            }

            $sisaJumlah = (float) $jumlah;

            // Hitung sisa masing-masing item dari DB (jumlah bayar aktual), bukan dari persentase
            // agar tidak ada floating-point drift akibat akumulasi cicilan.
            // Simpan nilai sementara di array PHP biasa (BUKAN properti model) agar tidak ikut di-save ke DB.
            $itemDataMap = [];   // [item_id => ['sisa' => float, 'terbayar' => float]]
            $itemsWithSisa = $items->filter(function ($item) use (&$itemDataMap) {
                $terbayarItem = (float) Pembayaran::where('item_uang_pangkal_id', $item->id)->sum('jumlah_bayar');
                $nominal      = (float) $item->nominal;
                $sisa         = max(0, round($nominal - $terbayarItem, 2));
                $itemDataMap[$item->id] = ['sisa' => $sisa, 'terbayar' => $terbayarItem];
                return $sisa > 0;
            });


            $totalSisaNominalItems = $itemsWithSisa->sum(fn($item) => $itemDataMap[$item->id]['sisa']);

            if ($totalSisaNominalItems <= 0) {
                throw new Exception('Semua item uang pangkal sudah lunas');
            }

            $itemsPaid    = [];
            $tanggalBayar = $tanggal ? Carbon::parse($tanggal) : Carbon::now();

            // Alokasi proporsional dengan pola "item terakhir dapat sisa persis"
            // agar total yang dialokasikan == $sisaJumlah (tidak ada selisih floating-point)
            $itemsList      = $itemsWithSisa->values();
            $unpaidCount    = $itemsList->count();
            $allocatedTotal = 0;

            foreach ($itemsList as $idx => $item) {
                $itemSisa     = $itemDataMap[$item->id]['sisa'];
                $terbayarLama = $itemDataMap[$item->id]['terbayar'];

                if ($itemId) {
                    $bayarItem = min($sisaJumlah, $itemSisa);
                } else {
                    $isLast = ($idx === $unpaidCount - 1);
                    if ($isLast) {
                        // Item terakhir dapat sisa persis agar tidak ada selisih
                        $bayarItem = min($itemSisa, max(0, round($sisaJumlah - $allocatedTotal, 2)));
                    } else {
                        $ratio     = $itemSisa / $totalSisaNominalItems;
                        // Bulatkan ke satuan rupiah (0 desimal) untuk menghindari drift pecahan sen
                        $bayarItem = min($itemSisa, round($sisaJumlah * $ratio));
                    }
                }

                if ($bayarItem <= 0) continue;

                $allocatedTotal += $bayarItem;

                // Hitung total terbayar aktual setelah cicilan ini
                $totalTerbayarItem = round($terbayarLama + $bayarItem, 2);
                $nominal           = (float) $item->nominal;

                // Cek lunas: total terbayar >= nominal (toleransi 1 rupiah)
                $isItemLunas = ($totalTerbayarItem >= $nominal - 1);

                // Update persentase dari jumlah rupiah nyata (bukan akumulasi desimal persentase)
                // Hanya update kolom yang ada di tabel (persentase_dibayar, status)
                $item->persentase_dibayar = $isItemLunas ? 100 : min(99.99, round(($totalTerbayarItem / $nominal) * 100, 4));
                $item->status             = $isItemLunas ? 'lunas' : 'belum_lunas';
                $item->save();

                Pembayaran::create([
                    'tagihan_id'            => $tagihan->id,
                    'item_uang_pangkal_id'  => $item->id,
                    'tanggal_bayar'         => $tanggalBayar,
                    'jumlah_bayar'          => $bayarItem,
                    'created_by'            => auth()->id() ?? (\App\Models\User::first()?->id ?? 1),
                ]);

                $itemsPaid[] = [
                    'item'       => $item->nama_item,
                    'jumlah'     => $bayarItem,
                    'persentase' => $item->persentase_dibayar,
                ];
            }

            // Update tagihan status — bandingkan total terbayar vs nominal dengan toleransi 1 rupiah
            $totalTerbayar = (float) $tagihan->pembayarans()->sum('jumlah_bayar');
            $allItemLunas  = $siswa->itemUangPangkals()->where('status', '!=', 'lunas')->count() === 0;

            if ($allItemLunas || ($totalTerbayar >= (float) $tagihan->nominal - 1 && (float) $tagihan->nominal > 0)) {
                $tagihan->update(['status' => 'lunas']);
            } else {
                $tagihan->update(['status' => 'belum_lunas']);
            }

            // $allocatedTotal adalah total yang benar-benar dialokasikan ke item-item UAP
            // ($sisaJumlah tidak pernah dikurangi dalam loop, sehingga tidak bisa dipakai di sini)
            $amountPaid = $allocatedTotal;
            if ($recordRiwayat && $amountPaid > 0) {
                RiwayatTransaksi::create([
                    'siswa_id'          => $siswaId,
                    'tanggal_transaksi' => $tanggalBayar ? Carbon::parse($tanggalBayar)->setTimeFrom(now()) : now(),
                    'function'          => 'Bayar',
                    'jenis'             => 'UAP',
                    'nominal'           => $amountPaid,
                    'sisa'              => max(0, (float) $tagihan->nominal - $totalTerbayar),
                    'nilai_akhir'       => null,
                    'created_by'        => auth()->id(),
                ]);
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Pembayaran uang pangkal berhasil',
                'items_paid' => $itemsPaid,
                'sisa_uang' => $sisaJumlah,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process SPP payment (can pay multiple months at once)
     * 
     * @param int $siswaId
     * @param array $bulanArray Array of months to pay [1, 2, 3]
     * @param int $tahun Year
     * @param int $tahunAjaranId Academic year ID
     * @return array Result with success status and message
     */
    public function bayarSPP($siswaId, array $bulanArray, $tahun, $tahunAjaranId, $recordRiwayat = true)
    {
        DB::beginTransaction();
        try {
            $siswa = Siswa::with('golongan')->findOrFail($siswaId);
            if (in_array(strtolower($siswa->status_siswa ?? ''), ['non_aktif', 'non aktif', 'pindah'])) {
                return ['success' => false, 'message' => 'Siswa berstatus Non-Aktif tidak dapat melakukan transaksi pembayaran.'];
            }

            $jenjangId = $siswa->golongan->jenjang_id ?? null;
            $kelasName = $siswa->golongan->nama_golongan ?? 'Kelas 1';
            $golonganKey  = $siswa->golongan_kategori ?? 'REG';
            $golonganAsal = $siswa->golongan_asal ?? (($golonganKey === 'ANP') ? 'ANP' : (($golonganKey === 'APK') ? 'APK' : 'REG'));
            // SPP untuk golongan APK mengikuti golongan REG
            $baseSppCat   = ($golonganAsal === 'ANP') ? 'ANP' : 'REG';
            $shorthandToKategori = [
                'REG'  => 'Reguler',
                'ANP'  => 'Anak Pegawai',
                'APK'  => 'Anak Pegawai Khusus',
                'FULL' => 'Pindahan Satu Tahun',
                'HALF' => 'Pindahan Setengah Tahun',
            ];
            $kategoriName = $shorthandToKategori[$baseSppCat] ?? 'Reguler';

            $settingSpp = \App\Models\SettingSpp::where('jenjang_id', $jenjangId)
                ->where('kelas', $kelasName)
                ->whereIn('kategori', [$baseSppCat, $kategoriName])
                ->first();

            $nominalSpp = $settingSpp ? (float) $settingSpp->nominal : (float) ($siswa->golongan->nominal_uang_spp ?? 0);
            
            $bulansPaid = [];
            $totalBayar = 0;

            foreach ($bulanArray as $bulan) {
                // Check or create tagihan
                $tagihan = Tagihan::where('siswa_id', $siswaId)
                    ->where('jenis', 'spp')
                    ->where('bulan', $bulan)
                    ->first();

                if (!$tagihan) {
                    $tagihan = Tagihan::create([
                        'siswa_id'        => $siswaId,
                        'tahun_ajaran_id' => $tahunAjaranId,
                        'jenis'           => 'spp',
                        'bulan'           => $bulan,
                        'tahun'           => $tahun,
                        'nominal'         => $nominalSpp,
                        'status'          => 'belum_lunas',
                    ]);
                } elseif ($tahunAjaranId && !$tagihan->tahun_ajaran_id) {
                    $tagihan->update(['tahun_ajaran_id' => $tahunAjaranId]);
                }

                // Check if already paid
                if ($tagihan->status === 'lunas') {
                    continue; // Skip already paid months
                }

                // Create pembayaran
                Pembayaran::create([
                    'tagihan_id' => $tagihan->id,
                    'tanggal_bayar' => Carbon::now(),
                    'jumlah_bayar' => $nominalSpp,
                    'created_by' => auth()->id() ?? (\App\Models\User::first()?->id ?? 1),
                ]);

                // Update tagihan status
                $tagihan->update(['status' => 'lunas']);

                $bulansPaid[] = $bulan;
                $totalBayar += $nominalSpp;
            }

            if ($recordRiwayat && $totalBayar > 0) {
                RiwayatTransaksi::create([
                    'siswa_id'          => $siswaId,
                    'tanggal_transaksi' => now(),
                    'function'          => 'Bayar',
                    'jenis'             => 'SPP',
                    'nominal'           => (float) $totalBayar,
                    'sisa'              => null,
                    'nilai_akhir'       => null,
                    'created_by'        => auth()->id(),
                ]);
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Pembayaran SPP berhasil',
                'bulans_paid' => $bulansPaid,
                'total_bayar' => $totalBayar,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get dashboard statistics
     * 
     * @param int $jenjangId
     * @param int|null $golonganId Filter by specific class
     * @param int|null $tahunAjaranId Filter by academic year
     * @return array Statistics data
     */
    public function getStatsDashboard($jenjangId, $golonganId = null, $tahunAjaranId = null)
    {
        // Get active academic year if not specified
        if (!$tahunAjaranId) {
            $tahunAjaran = \App\Models\Tahun_ajaran::getAktif();
            $tahunAjaranId = $tahunAjaran ? $tahunAjaran->id : null;
        }

        // Base query for students
        $siswaQuery = Siswa::byJenjang($jenjangId)->aktif();
        
        if ($golonganId) {
            $siswaQuery->byGolongan($golonganId);
        }

        $totalSiswa = $siswaQuery->count();

        // Get tagihan statistics
        $tagihanQuery = Tagihan::whereHas('siswa', function ($q) use ($jenjangId, $golonganId) {
            $q->byJenjang($jenjangId)->aktif();
            if ($golonganId) {
                $q->byGolongan($golonganId);
            }
        });

        if ($tahunAjaranId) {
            $tagihanQuery->where('tahun_ajaran_id', $tahunAjaranId);
        }

        // SPP Statistics
        $sppBelumLunas = (clone $tagihanQuery)->byJenis('spp')->belumLunas()->sum('nominal');
        $sppLunas = (clone $tagihanQuery)->byJenis('spp')->lunas()->sum('nominal');

        // Uang Pangkal Statistics (from item_uang_pangkals)
        $itemQuery = ItemUangPangkal::whereHas('siswa', function ($q) use ($jenjangId, $golonganId) {
            $q->byJenjang($jenjangId)->aktif();
            if ($golonganId) {
                $q->byGolongan($golonganId);
            }
        });

        if ($tahunAjaranId) {
            $itemQuery->where('tahun_ajaran_id', $tahunAjaranId);
        }

        $itemsUangPangkal = $itemQuery->with('pembayarans')->get();
        $uangPangkalTotal = $itemsUangPangkal->sum('nominal');
        $uangPangkalTerbayar = $itemsUangPangkal->sum('nominal_terbayar');
        $uangPangkalBelumLunas = $itemsUangPangkal->sum('sisa_nominal');

        // Today's income
        $pendapatanHariIni = Pembayaran::whereHas('tagihan.siswa', function ($q) use ($jenjangId, $golonganId) {
            $q->byJenjang($jenjangId)->aktif();
            if ($golonganId) {
                $q->byGolongan($golonganId);
            }
        })->today()->sum('jumlah_bayar');

        // This month's income
        $pendapatanBulanIni = Pembayaran::whereHas('tagihan.siswa', function ($q) use ($jenjangId, $golonganId) {
            $q->byJenjang($jenjangId)->aktif();
            if ($golonganId) {
                $q->byGolongan($golonganId);
            }
        })->thisMonth()->sum('jumlah_bayar');

        return [
            'total_siswa' => $totalSiswa,
            'total_uang_pangkal' => $uangPangkalTotal,
            'uang_pangkal_terbayar' => $uangPangkalTerbayar,
            'uang_pangkal_belum_lunas' => $uangPangkalBelumLunas,
            'spp_belum_lunas' => $sppBelumLunas,
            'spp_lunas' => $sppLunas,
            'pendapatan_hari_ini' => $pendapatanHariIni,
            'pendapatan_bulan_ini' => $pendapatanBulanIni,
            'total_tunggakan' => $sppBelumLunas + $uangPangkalBelumLunas,
        ];
    }

    /**
     * Get recent transactions
     * 
     * @param int $jenjangId
     * @param int $limit
     * @param int|null $golonganId Filter by specific class
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTransaksiTerbaru($jenjangId, $limit = 10, $golonganId = null)
    {
        $raw = Pembayaran::with(['tagihan.siswa.golongan', 'user'])
            ->whereHas('tagihan.siswa', function ($q) use ($jenjangId, $golonganId) {
                $q->byJenjang($jenjangId)->aktif();
                if ($golonganId) {
                    $q->byGolongan($golonganId);
                }
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(200)
            ->get();

        $grouped = $raw->groupBy(function ($pembayaran) {
            $siswaId = $pembayaran->tagihan->siswa_id ?? 0;
            $jenis   = $pembayaran->tagihan->jenis ?? 'spp';
            $dateStr = $pembayaran->tanggal_bayar 
                ? Carbon::parse($pembayaran->tanggal_bayar)->format('Y-m-d') 
                : ($pembayaran->created_at ? $pembayaran->created_at->format('Y-m-d') : '');

            return $siswaId . '_' . $jenis . '_' . $dateStr;
        });

        $result = collect();

        foreach ($grouped as $group) {
            $first = clone $group->first();
            $first->jumlah_bayar = (float) $group->sum('jumlah_bayar');
            $result->push($first);

            if ($result->count() >= $limit) {
                break;
            }
        }

        return $result;
    }

    /**
     * Get siswa with tunggakan
     * 
     * @param int $jenjangId
     * @param int|null $golonganId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSiswaWithTunggakan($jenjangId, $golonganId = null)
    {
        return Siswa::byJenjang($jenjangId)
            ->aktif()
            ->with(['golongan', 'tagihans', 'itemUangPangkals'])
            ->when($golonganId, function ($q) use ($golonganId) {
                $q->byGolongan($golonganId);
            })
            ->get()
            ->filter(function ($siswa) {
                return $siswa->total_tunggakan_spp > 0 || $siswa->total_tunggakan_uang_pangkal > 0;
            });
    }
}
