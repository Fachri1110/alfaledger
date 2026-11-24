{{-- Dynamic Transaction Rows --}}
@php
    $golonganColor = ['REG' => 'bg-blue-600', 'ANP' => 'bg-signature', 'APK' => 'bg-purple-600', 'FULL' => 'bg-emerald-600', 'HALF' => 'bg-amber-500'];
@endphp
@forelse($transaksi ?? [] as $t)
    @php
        if (is_array($t)) {
            $trxNama     = $t['nama'];
            $trxKelas    = $t['kelas'] ?? '-';
            $trxNominal  = $t['nominal'];
            $trxTanggal  = $t['tanggal'] ?? '-';
            $trxTipe     = $t['tipe'] ?? '-';
            $trxGolongan = $t['golongan'] ?? 'REG';
        } else {
            $trxNama     = $t->tagihan->siswa->nama_siswa ?? 'N/A';
            $trxKelas    = $t->tagihan->siswa->golongan->nama_golongan ?? '-';
            $trxNominal  = $t->jumlah_bayar ?? 0;
            $trxTanggal  = $t->tanggal_bayar?->translatedFormat('d M Y') ?? ($t->tanggal_bayar?->format('d M Y') ?? '-');
            $trxTipe     = ($t->tagihan->jenis ?? '') === 'spp' ? 'SPP' : 'UAP';
            $trxGolongan = $t->tagihan->siswa->golongan_kategori ?? 'REG';
        }
    @endphp
    <tr class="hover:bg-shade-white-i/30 transition-colors">
        <td class="px-6 py-4 font-medium whitespace-nowrap">{{ $trxNama }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-black/70">{{ $trxKelas }}</td>
        <td class="px-6 py-4 text-center whitespace-nowrap font-semibold">Rp {{ number_format($trxNominal, 0, ',', '.') }}</td>
        <td class="px-6 py-4 text-center whitespace-nowrap text-black/70">{{ $trxTanggal }}</td>
        <td class="px-6 py-4 text-center whitespace-nowrap">{{ $trxTipe }}</td>
        <td class="px-6 py-4 text-center whitespace-nowrap">
            <span class="inline-block {{ $golonganColor[$trxGolongan] ?? 'bg-black/50' }} text-white text-xs font-bold rounded-full px-4 py-1.5">{{ $trxGolongan }}</span>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="px-6 py-8 text-center text-black/50">
            <div class="flex flex-col items-center gap-2">
                <span class="font-medium">Belum ada transaksi</span>
            </div>
        </td>
    </tr>
@endforelse

