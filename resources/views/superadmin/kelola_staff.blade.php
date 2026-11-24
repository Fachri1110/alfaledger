{{-- ===== TOAST NOTIFICATION ===== --}}
<div
    x-data="{ show: false, message: '' }"
    x-init="
        @if (session('success'))
            (() => {
                const raw = @js(session('success'));
                let msg = raw;
                if (raw.includes('tambah')) { msg = 'Berhasil menambahkan!'; }
                else if (raw.includes('perbarui') || raw.includes('edit')) { msg = 'Berhasil mengedit!'; }
                else if (raw.includes('hapus')) { msg = 'Berhasil menghapus!'; }
                message = msg;
                show = true;
                clearTimeout(window.__toastTimer);
                window.__toastTimer = setTimeout(() => show = false, 7000);
            })();
        @endif
    "
    x-on:notify.window="
        message = $event.detail.message;
        show = true;
        clearTimeout(window.__toastTimer);
        window.__toastTimer = setTimeout(() => show = false, 7000);
    "
    x-show="show"
    x-transition
    x-cloak
    class="fixed bottom-5 right-5 z-[9999] bg-signature text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
    </svg>
    <span class="text-sm font-semibold font-inter" x-text="message"></span>
</div>

{{-- ===== TAB CONTENT: KELOLA STAF ===== --}}
<div x-show="activeTab === 'kelola_staf'" x-transition class="space-y-6" x-cloak>

    {{-- Flash Message Alerts (error / validation only, success now uses toast) --}}
    @if (session('error'))
        <div class="px-4 py-3 rounded-lg bg-danger/10 border border-danger/20 text-danger text-sm font-medium shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-danger/10 border border-danger/20 text-danger text-sm font-medium shadow-sm">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Control Bar --}}
    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
        {{-- Search Bar --}}
        <div class="relative flex-grow">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-black/50">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.637 10.637z" />
                </svg>
            </span>
            <input
                type="text"
                x-model="searchQuery"
                placeholder="Cari staf..."
                class="w-full pl-11 pr-4 py-3 rounded-lg border border-shade-white-ii focus:border-signature focus:ring-1 focus:ring-signature outline-none text-base-spp text-black font-inter bg-white shadow-sm transition-all placeholder-black/30">
        </div>

        {{-- Urutkan (Sort) --}}
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open"
                class="w-full md:w-auto bg-white hover:bg-shade-white-i/50 text-black px-6 py-3 rounded-lg border border-shade-white-ii font-bold text-sm font-inter flex items-center justify-center gap-2 cursor-pointer shadow-sm transition-colors min-h-[46px]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-black">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <span>Urutkan</span>
            </button>
            <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white border border-shade-white-ii rounded-lg shadow-lg z-50 overflow-hidden font-inter py-1">
                <button @click="sortBy = 'none'; open = false" :class="sortBy === 'none' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'" class="w-full text-left px-4 py-2.5 text-sm transition-colors">Urutan Default</button>
                <button @click="sortBy = 'asc'; open = false" :class="sortBy === 'asc' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'" class="w-full text-left px-4 py-2.5 text-sm transition-colors font-semibold">Abjad A - Z</button>
                <button @click="sortBy = 'desc'; open = false" :class="sortBy === 'desc' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'" class="w-full text-left px-4 py-2.5 text-sm transition-colors font-semibold">Abjad Z - A</button>
            </div>
        </div>

        {{-- Filter --}}
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open"
                class="w-full md:w-auto bg-white hover:bg-shade-white-i/50 text-black px-6 py-3 rounded-lg border border-shade-white-ii font-bold text-sm font-inter flex items-center justify-center gap-2 cursor-pointer shadow-sm transition-colors min-h-[46px]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-black">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                <span>Filter</span>
            </button>
            <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white border border-shade-white-ii rounded-lg shadow-lg z-50 overflow-hidden font-inter py-1">
                <button @click="filterJenjang = 'all'; open = false" :class="filterJenjang === 'all' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'" class="w-full text-left px-4 py-2.5 text-sm transition-colors">Semua Jenjang</button>
                <button @click="filterJenjang = 'YAYASAN'; open = false" :class="filterJenjang === 'YAYASAN' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'" class="w-full text-left px-4 py-2.5 text-sm transition-colors">Yayasan</button>
                @foreach ($jenjangs as $jen)
                    <button @click="filterJenjang = '{{ $jen->nama_jenjang }}'; open = false" :class="filterJenjang === '{{ $jen->nama_jenjang }}' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'" class="w-full text-left px-4 py-2.5 text-sm transition-colors">{{ $jen->nama_jenjang }}</button>
                @endforeach
            </div>
        </div>

        {{-- Tambah Staf Button --}}
        <button @click="openAddModal()"
            class="bg-signature hover:opacity-90 text-white px-5 py-3 rounded-lg font-bold text-sm font-inter transition-all flex items-center justify-center gap-2 cursor-pointer shadow-sm min-h-[46px]">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 text-white">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>Tambah Staf</span>
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-shade-white-ii overflow-hidden">
        <div class="bg-signature text-white px-6 py-4 font-bold text-base-spp">Daftar Staf</div>
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black text-white font-inter text-sm font-semibold uppercase">
                        <th class="px-6 py-4 w-full">Username</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap w-32">Akses</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap w-32">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-shade-white-i font-inter text-sm text-black bg-white">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <tr class="hover:bg-shade-white-i/30 transition-colors">
                            <td class="px-6 py-4 font-bold text-black w-full">
                                <button type="button" @click="openEditModal(user)" class="flex items-center gap-3 text-left transition-colors cursor-pointer outline-none group">
                                    <img :src="user.avatar" alt="Avatar" class="w-10 h-10 rounded-full object-cover shadow-sm border border-shade-white-ii shrink-0">
                                    <div>
                                        <span class="block font-bold text-black group-hover:text-signature transition-colors" x-text="user.name"></span>
                                        <div class="text-xs text-black/40 font-semibold mt-0.5" x-text="'@' + user.username"></div>
                                    </div>
                                </button>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap w-32">
                                <span :class="user.role === 'Ketua Yayasan' || user.role === 'Staf Yayasan' ? 'bg-signature' : 'bg-black'" class="text-white px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider inline-block text-center min-w-[75px]" x-text="user.role === 'Ketua Yayasan' || user.role === 'Staf Yayasan' ? 'Yayasan' : user.jenjang_nama"></span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap w-32 font-bold text-black/70" x-text="user.role"></td>
                        </tr>
                    </template>
                    <tr x-show="filteredUsers.length === 0" x-cloak>
                        <td colspan="3" class="px-6 py-12 text-center text-black/50 font-medium bg-white">Tidak ada data staf yang cocok.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ===== MODAL: TAMBAH STAF ===== --}}
<div x-show="showAddModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 transition-opacity" @keydown.escape.window="showAddModal = false">
    <div @click.away="showAddModal = false" class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full transition-all" x-transition>
        {{-- Purple Header --}}
        <div class="bg-signature text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                <h3 class="text-base font-bold font-inter">Tambah Staf</h3>
            </div>
            <button @click="showAddModal = false" class="text-white/80 hover:text-white cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        @php
            $userRolePrefix = auth()->user()?->hasRole('Ketua Yayasan') ? 'ketuayayasan' : (auth()->user()?->hasRole('Staf Yayasan') ? 'stafyayasan' : 'superadmin');
        @endphp
        <form action="{{ route($userRolePrefix . '.users.store') }}" method="POST">
            @csrf
            <input type="hidden" name="role" :value="addRole" required>
            <input type="hidden" name="jenjang_id" :value="addJenjangId" :required="addRole === 'Staf TU'">

            <div class="p-6 space-y-6">
                {{-- Fields Row --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block mb-1.5 text-sm font-bold text-black">Username</label>
                        <input type="text" name="username" required class="w-full px-4 py-2.5 rounded-lg border border-shade-white-ii focus:border-signature focus:ring-2 focus:ring-signature/10 outline-none text-sm text-black">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-bold text-black">Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-2.5 rounded-lg border border-shade-white-ii focus:border-signature focus:ring-2 focus:ring-signature/10 outline-none text-sm text-black">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-bold text-black">Kata Sandi</label>
                        <input type="password" name="password" required class="w-full px-4 py-2.5 rounded-lg border border-shade-white-ii focus:border-signature focus:ring-2 focus:ring-signature/10 outline-none text-sm text-black">
                    </div>

                    {{-- Akses dropdown --}}
                    <div>
                        <label class="block mb-1.5 text-sm font-bold text-black">Akses</label>
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button type="button" @click="open = !open"
                                class="w-full bg-white px-4 py-2.5 rounded-lg border border-shade-white-ii focus:border-signature text-sm flex items-center justify-between cursor-pointer hover:border-signature/50 transition-colors">
                                <span :class="addAksesLabel === '' ? 'text-black/30' : 'text-black font-medium'" x-text="addAksesLabel === '' ? 'Pilih Akses' : addAksesLabel"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-black/50 transition-transform shrink-0" :class="open ? 'rotate-180' : ''">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div x-show="open" x-transition x-cloak class="absolute left-0 right-0 mt-2 bg-white border border-shade-white-ii rounded-lg shadow-lg z-50 overflow-hidden font-inter py-1">
                                @if(auth()->user()?->hasRole('Ketua Yayasan'))
                                    <button type="button" @click="addRole = 'Staf Yayasan'; addJenjangId = ''; addAksesLabel = 'Staf Yayasan'; open = false"
                                        :class="addRole === 'Staf Yayasan' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'"
                                        class="w-full text-left px-4 py-2.5 text-sm transition-colors">Staf Yayasan</button>
                                @endif
                                @foreach ($jenjangs as $jenjang)
                                    <button type="button" @click="addRole = 'Staf TU'; addJenjangId = '{{ $jenjang->id }}'; addAksesLabel = '{{ $jenjang->nama_jenjang }}'; open = false"
                                        :class="(addRole === 'Staf TU' && addJenjangId == '{{ $jenjang->id }}') ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'"
                                        class="w-full text-left px-4 py-2.5 text-sm transition-colors">{{ $jenjang->nama_jenjang }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-[#F2F2F2] border-t border-shade-white-ii px-6 py-4 flex items-center justify-end gap-3 rounded-b-2xl">
                <span class="text-sm text-black/60 font-medium mr-auto">Tambah staf baru?</span>
                <button type="button" @click="showAddModal = false" class="flex items-center gap-1.5 px-4 py-2 rounded-lg border border-shade-white-ii text-black text-sm font-semibold hover:bg-white cursor-pointer transition-colors bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    Batal
                </button>
                <button type="submit" class="flex items-center gap-1.5 bg-[#D6B5DB] hover:bg-[#C9A2CE] text-signature px-5 py-2 rounded-lg text-sm font-semibold cursor-pointer transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL: EDIT STAF ===== --}}
<div x-show="showEditModal" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 transition-opacity" @keydown.escape.window="showEditModal = false">
    <div @click.away="showEditModal = false" class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full transition-all" x-transition>
        {{-- Purple Header --}}
        <div class="bg-signature text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                <h3 class="text-base font-bold font-inter">Detail Staf</h3>
            </div>
            <button @click="showEditModal = false" class="text-white/80 hover:text-white cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form :action="`{{ url('/') }}/${'{{ $userRolePrefix }}'}/users/${editUser.id}`" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="role" :value="editUser.role" required>
            <input type="hidden" name="jenjang_id" :value="(editUser.role === 'Ketua Yayasan' || editUser.role === 'Staf Yayasan') ? '' : editUser.jenjang_id" :required="editUser.role === 'Staf TU'">

            <div class="p-6 space-y-6">
                {{-- Foto Profil (tombol "Ubah" dihapus) --}}
                <div>
                    <p class="text-sm font-bold text-black mb-3">Foto Profil</p>
                    <div class="flex items-center gap-3">
                        <template x-if="editUser.avatar && !editUser.avatar.includes('ui-avatars.com')">
                            <img :src="editUser.avatar" class="w-14 h-14 rounded-full object-cover shadow-sm border border-shade-white-ii shrink-0">
                        </template>
                        <template x-if="!editUser.avatar || editUser.avatar.includes('ui-avatars.com')">
                            <div class="w-14 h-14 rounded-full bg-signature text-white flex items-center justify-center text-xl font-bold shrink-0 shadow-sm">
                                <span x-text="editUser.username ? editUser.username.charAt(0).toUpperCase() : 'S'"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Fields Row --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block mb-1.5 text-sm font-bold text-black">Username</label>
                        <input type="text" name="username" x-model="editUser.username" required class="w-full px-4 py-2.5 rounded-lg border border-shade-white-ii focus:border-signature focus:ring-2 focus:ring-signature/10 outline-none text-sm text-black">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-bold text-black">Email</label>
                        <input type="email" name="email" x-model="editUser.email" required class="w-full px-4 py-2.5 rounded-lg border border-shade-white-ii focus:border-signature focus:ring-2 focus:ring-signature/10 outline-none text-sm text-black">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-bold text-black">Kata Sandi</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="w-full px-4 py-2.5 rounded-lg border border-shade-white-ii focus:border-signature focus:ring-2 focus:ring-signature/10 outline-none text-sm text-black placeholder-black/30">
                    </div>

                    {{-- Akses dropdown --}}
                    <div>
                        <label class="block mb-1.5 text-sm font-bold text-black">Akses</label>
                        <div class="relative" x-data="{ open: false, jenjangList: {{ Illuminate\Support\Js::from($jenjangs->map(fn($j) => ['id' => (string) $j->id, 'nama' => $j->nama_jenjang])->values()) }} }" @click.away="open = false">
                            @php
                                $canEditYayasanRole = auth()->user()?->hasRole('Ketua Yayasan') || auth()->user()?->hasRole('Super Admin');
                            @endphp

                            <template x-if="editUser.role === 'Ketua Yayasan' || (editUser.role === 'Staf Yayasan' && {{ $canEditYayasanRole ? 'false' : 'true' }})">
                                <div class="w-full bg-shade-white-i px-4 py-2.5 rounded-lg border border-shade-white-ii text-black/50 text-sm cursor-not-allowed font-medium" x-text="editUser.role"></div>
                            </template>

                            <template x-if="editUser.role !== 'Ketua Yayasan' && (editUser.role !== 'Staf Yayasan' || {{ $canEditYayasanRole ? 'true' : 'false' }})">
                                <div>
                                    <button type="button" @click="open = !open"
                                        class="w-full bg-white px-4 py-2.5 rounded-lg border border-shade-white-ii focus:border-signature text-sm flex items-center justify-between cursor-pointer hover:border-signature/50 transition-colors">
                                        <span class="text-black font-medium" x-text="editUser.role === 'Staf Yayasan' ? 'Staf Yayasan' : ((jenjangList.find(j => j.id == editUser.jenjang_id) || {}).nama || 'Pilih Akses')"></span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 text-black/50 transition-transform shrink-0" :class="open ? 'rotate-180' : ''">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition x-cloak class="absolute left-0 right-0 mt-2 bg-white border border-shade-white-ii rounded-lg shadow-lg z-50 overflow-hidden font-inter py-1">
                                        @if(auth()->user()?->hasRole('Ketua Yayasan') || auth()->user()?->hasRole('Super Admin'))
                                            <button type="button" @click="editUser.role = 'Staf Yayasan'; editUser.jenjang_id = ''; open = false"
                                                :class="editUser.role === 'Staf Yayasan' ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'"
                                                class="w-full text-left px-4 py-2.5 text-sm transition-colors">Staf Yayasan</button>
                                        @endif
                                        @foreach ($jenjangs as $jenjang)
                                            <button type="button" @click="editUser.role = 'Staf TU'; editUser.jenjang_id = '{{ $jenjang->id }}'; open = false"
                                                :class="(editUser.role === 'Staf TU' && editUser.jenjang_id == '{{ $jenjang->id }}') ? 'bg-signature/10 text-signature font-bold' : 'text-black/80 hover:bg-shade-white-i/30'"
                                                class="w-full text-left px-4 py-2.5 text-sm transition-colors">{{ $jenjang->nama_jenjang }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="bg-[#F2F2F2] border-t border-shade-white-ii px-6 py-4 flex items-center justify-end gap-3 rounded-b-2xl">
                <span class="text-sm text-black/60 font-medium mr-auto">Simpan perubahan?</span>
                <button type="button" @click="showEditModal = false" class="flex items-center gap-1.5 px-4 py-2 rounded-lg border border-shade-white-ii text-black text-sm font-semibold hover:bg-white cursor-pointer transition-colors bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    Batal
                </button>
                <button type="submit" class="flex items-center gap-1.5 bg-[#D6B5DB] hover:bg-[#C9A2CE] text-signature px-5 py-2 rounded-lg text-sm font-semibold cursor-pointer transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Simpan
                </button>
                {{-- Delete Button (Ketua Yayasan Only) --}}
                @if (auth()->user()?->hasRole('Ketua Yayasan'))
                    <template x-if="editUser.id !== {{ auth()->id() }}">
                        <button type="button" @click.prevent="deleteUser(editUser.id); showEditModal = false" class="flex items-center justify-center w-10 h-10 bg-danger hover:bg-red-700 text-white rounded-lg cursor-pointer transition-colors shrink-0" title="Hapus Staf">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </button>
                    </template>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Hidden Delete Form --}}
<form id="delete-user-form" action="" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>