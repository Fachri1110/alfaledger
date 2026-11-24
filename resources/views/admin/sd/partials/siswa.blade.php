{{-- ===== KELOLA SISWA SD ===== --}}
<div class="bg-white rounded-xl border border-shade-white-ii shadow-sm overflow-hidden font-inter">

    {{-- Table Header --}}
    <div class="bg-signature text-white px-6 py-4 font-bold text-base-spp flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            <span>Data Siswa</span>
        </div>
        {{-- Kelas indicator --}}
        <span class="text-sm font-normal text-white/70" x-text="'Kelas: ' + $store.sd.kelasLabel($store.sd.activeKelas)"></span>
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
            Tambah Siswa
        </button>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-black text-white text-sm font-semibold uppercase">
                    <th class="px-6 py-3.5">NISN</th>
                    <th class="px-6 py-3.5">Nama Lengkap</th>
                    <th class="px-6 py-3.5">Kelas</th>
                    <th class="px-6 py-3.5">Golongan</th>
                    <th class="px-6 py-3.5">Tahun Ajaran</th>
                    <th class="px-6 py-3.5 text-center">Status</th>
                    <th class="px-6 py-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-shade-white-i text-sm text-black">
                <tr class="hover:bg-shade-white-i/30 transition-colors">
                    <td class="px-6 py-4 font-mono text-xs">1234567890</td>
                    <td class="px-6 py-4 font-medium">Alya Putri Maharani</td>
                    <td class="px-6 py-4 text-black/70">Kelas 1</td>
                    <td class="px-6 py-4 text-black/70">Golongan A</td>
                    <td class="px-6 py-4 text-black/70">2025/2026</td>
                    <td class="px-6 py-4 text-center">
                        <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">Aktif</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition-colors cursor-pointer" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                </svg>
                            </button>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 hover:bg-red-100 text-danger transition-colors cursor-pointer" title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
