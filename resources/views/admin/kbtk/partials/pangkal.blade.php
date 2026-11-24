{{-- ===== KELOLA UANG PANGKAL KB/TK ===== --}}
<div class="bg-white rounded-xl border border-shade-white-ii shadow-sm overflow-hidden font-inter">

    {{-- Table Header --}}
    <div class="bg-signature text-white px-6 py-4 font-bold text-base-spp flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
            </svg>
            <span>Data Uang Pangkal</span>
        </div>
        {{-- Kelas indicator --}}
        <span class="text-sm font-normal text-white/70" x-text="'Kelas: ' + kelasLabel(activeKelas)"></span>
    </div>

    {{-- Controls --}}
    <div class="px-6 py-4 flex flex-col md:flex-row items-stretch md:items-center gap-3 border-b border-shade-white-ii bg-[#F2F2F2]/50">
        <div class="relative flex-grow">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-black/40">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.637 10.637z" />
                </svg>
            </span>
            <input type="text" placeholder="Cari nama siswa..." class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-shade-white-ii text-sm outline-none focus:border-signature bg-white">
        </div>
        <button class="bg-signature hover:opacity-90 text-white px-5 py-2.5 rounded-lg font-bold text-sm flex items-center gap-2 transition-all cursor-pointer shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Tambah Pembayaran
        </button>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-black text-white text-sm font-semibold uppercase">
                    <th class="px-6 py-3.5">Nama Siswa</th>
                    <th class="px-6 py-3.5">Kelas</th>
                    <th class="px-6 py-3.5">Total Tagihan</th>
                    <th class="px-6 py-3.5">Terbayar</th>
                    <th class="px-6 py-3.5">Sisa</th>
                    <th class="px-6 py-3.5 text-center">Status</th>
                    <th class="px-6 py-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-shade-white-i text-sm text-black">
                <tr class="hover:bg-shade-white-i/30 transition-colors">
                    <td class="px-6 py-4 font-medium">Alya Putri</td>
                    <td class="px-6 py-4 text-black/70">KB</td>
                    <td class="px-6 py-4">Rp 1.500.000</td>
                    <td class="px-6 py-4 text-signature font-semibold">Rp 1.500.000</td>
                    <td class="px-6 py-4 text-black/50">—</td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">Lunas</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button class="w-8 h-8 flex items-center justify-center mx-auto rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            </svg>
                        </button>
                    </td>
                </tr>
                <tr class="hover:bg-shade-white-i/30 transition-colors">
                    <td class="px-6 py-4 font-medium">Bima Eka</td>
                    <td class="px-6 py-4 text-black/70">TK A</td>
                    <td class="px-6 py-4">Rp 1.500.000</td>
                    <td class="px-6 py-4 text-signature font-semibold">Rp 750.000</td>
                    <td class="px-6 py-4 text-danger font-semibold">Rp 750.000</td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1 rounded-full">Cicilan</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button class="w-8 h-8 flex items-center justify-center mx-auto rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            </svg>
                        </button>
                    </td>
                </tr>
                <tr class="hover:bg-shade-white-i/30 transition-colors">
                    <td class="px-6 py-4 font-medium">Citra Dewi</td>
                    <td class="px-6 py-4 text-black/70">TK B</td>
                    <td class="px-6 py-4">Rp 1.500.000</td>
                    <td class="px-6 py-4 text-black/50">—</td>
                    <td class="px-6 py-4 text-danger font-semibold">Rp 1.500.000</td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-red-100 text-danger text-xs font-bold px-3 py-1 rounded-full">Belum Bayar</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button class="w-8 h-8 flex items-center justify-center mx-auto rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition-colors cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                            </svg>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
