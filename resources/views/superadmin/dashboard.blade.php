@extends('components.layouts.app')

@section('content')
<div x-data="staffApp()" class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">

    {{-- ===== TABS NAVIGATION ===== --}}
    <div class="flex border-b border-shade-white-ii mb-6">
        <button 
            @click="activeTab = 'dashboard'"
            :class="activeTab === 'dashboard' ? 'bg-[#F2F2F2] border-b-2 border-signature text-signature font-bold' : 'text-black/60 hover:text-black hover:bg-black/[0.02]'"
            class="px-6 py-3 font-inter text-base-spp transition-all cursor-pointer outline-none">
            Dashboard
        </button>
        <button 
            @click="activeTab = 'kelola_staf'"
            :class="activeTab === 'kelola_staf' ? 'bg-[#F2F2F2] border-b-2 border-signature text-signature font-bold' : 'text-black/60 hover:text-black hover:bg-black/[0.02]'"
            class="px-6 py-3 font-inter text-base-spp transition-all cursor-pointer outline-none">
            Kelola Staf
        </button>
    </div>

    {{-- ===== TAB CONTENT: DASHBOARD ===== --}}
    <div x-show="activeTab === 'dashboard'" x-transition class="space-y-6">
        
        {{-- Banner Halo Darlina --}}
        <div class="bg-cover bg-center rounded-2xl p-8 text-white shadow-[0_4px_20px_rgba(120,0,139,0.15)] flex flex-col justify-center min-h-[160px] relative overflow-hidden"
             style="background-image: url('{{ asset('images/headline.png') }}');">
            <span class="text-base mb-1">
                Yayasan Sekolah Alfa Omega
            </span>
            <h1 class="text-heading-spp font-bold font-inter leading-none">
                Halo, {{ auth()->user()->name ?? 'Darlina' }}
            </h1>
        </div>

        @php
            $user = auth()->user();
            $rolePrefix = $user?->hasRole('Ketua Yayasan') ? 'ketuayayasan.' : ($user?->hasRole('Staf Yayasan') ? 'stafyayasan.' : 'superadmin.');
        @endphp

        {{-- 3 Tombol Monitoring Jenjang --}}
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4 font-inter">
         
            {{-- SD --}}
            <a href="{{ route($rolePrefix . 'sd.dashboard') }}" class="w-full bg-signature hover:opacity-90 text-white py-4 px-6 rounded-xl flex items-center justify-center gap-3 transition-all font-bold text-base-spp shadow-sm group">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 shrink-0 group-hover:translate-x-0.5 transition-transform">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 15l3-3m0 0l-3-3m3 3h-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Masuk Ke SD</span>
            </a>

           
        </div>

        {{-- Tabel Aktivitas Terbaru --}}
        <div class="bg-white rounded-xl shadow-sm border border-shade-white-ii overflow-hidden">
            <div class="bg-signature text-white px-6 py-4 font-bold text-base-spp">
                Aktivitas Terbaru
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-black text-white font-inter text-sm font-semibold">
                            <th class="px-6 py-3.5 w-full">Username</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap">Aktivitas</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap">Waktu</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap">Tanggal</th>
                            <th class="px-6 py-3.5 text-center whitespace-nowrap">Akses</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-shade-white-i font-inter text-sm text-black">
                        @forelse ($activityLogs ?? [] as $log)
                            <tr class="hover:bg-shade-white-i/30 transition-colors">
                                <td class="px-6 py-4 font-medium w-full">{{ $log->username }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap font-medium">
                                    <span class="{{ $log->aktivitas === 'Masuk' ? 'text-emerald-600 font-semibold' : 'text-rose-600 font-semibold' }}">
                                        {{ $log->aktivitas }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap text-black/70">{{ $log->created_at->format('H:i') }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap text-black/70">{{ $log->created_at->translatedFormat('d F Y') }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="{{ $log->akses === 'ALL' ? 'bg-signature text-white' : 'bg-black text-white' }} px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider inline-block">
                                        {{ $log->akses }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-black/50 font-medium">Belum ada log aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @include('superadmin.kelola_staff')

@push('scripts')
<script>
    function staffApp() {
        return {
            activeTab: '{{ session('success') || session('error') || $errors->any() ? 'kelola_staf' : 'dashboard' }}',
            showAddModal: false,
            showEditModal: false,
            editUser: { id: '', name: '', username: '', email: '', role: 'Admin', jenjang_id: '' },
            searchQuery: '',
            sortBy: 'none',
            filterJenjang: 'all',
            users: {!! json_encode($users->map(function($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'email' => $u->email ?? '',
                    'role' => $u->roles->first()?->name ?? 'Staf TU',
                    'jenjang_id' => $u->jenjang_id,
                    'jenjang_nama' => $u->jenjang->nama_jenjang ?? '-',
                    'avatar' => file_exists(public_path('images/' . strtolower($u->username) . '.png')) 
                        ? asset('images/' . strtolower($u->username) . '.png') 
                        : 'https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&background=78008B&color=fff'
                ];
            })) !!},
            get filteredUsers() {
                let q = this.searchQuery.toLowerCase().trim();
                let list = this.users.filter(u => {
                    let matchesSearch = !q || 
                        u.name.toLowerCase().includes(q) || 
                        u.username.toLowerCase().includes(q) || 
                        u.email.toLowerCase().includes(q);

                    let matchesFilter = true;
                    if (this.filterJenjang !== 'all') {
                        if (this.filterJenjang === 'YAYASAN') {
                            matchesFilter = u.role === 'Ketua Yayasan' || u.role === 'Staf Yayasan';
                        } else {
                            matchesFilter = u.jenjang_nama === this.filterJenjang;
                        }
                    }

                    return matchesSearch && matchesFilter;
                });

                if (this.sortBy === 'asc') {
                    list.sort((a, b) => a.name.localeCompare(b.name));
                } else if (this.sortBy === 'desc') {
                    list.sort((a, b) => b.name.localeCompare(a.name));
                }

                return list;
            },
            addRole: '',
            addJenjangId: '',
            addAksesLabel: '',
            openAddModal() {
                this.addRole = '';
                this.addJenjangId = '';
                this.addAksesLabel = '';
                this.showAddModal = true;
            },
            openEditModal(user) {
                this.editUser = {
                    id: user.id,
                    name: user.name,
                    username: user.username,
                    email: user.email,
                    role: user.role,
                    jenjang_id: user.jenjang_id || '',
                    avatar: user.avatar
                };
                this.showEditModal = true;
            },
            deleteUser(id) {
                if (confirm('Apakah Anda yakin ingin menghapus staf ini?')) {
                    let form = document.getElementById('delete-user-form');
                    let prefix = "{{ $rolePrefix }}".replace('.', '');
                    form.action = `{{ url('/') }}/${prefix}/users/${id}`;
                    form.submit();
                }
            }
        };
    }
</script>
@endpush
@endsection