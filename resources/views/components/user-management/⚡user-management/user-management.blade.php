<div class="min-h-screen bg-white p-4 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        {{-- HEADER SECTION: KOTAK JUDUL (Seragam dengan Dashboard & Employee) --}}
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">User Management</h2>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">
                        Kelola akses internal personil HC
                    </p>
                </div>

                {{-- SEARCH & BUTTON AREA --}}
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <div class="relative flex-1 md:flex-none">
                        <input wire:model.live="search" type="text" placeholder="Cari personil..."
                            class="w-full md:w-64 bg-slate-50 border-none rounded-2xl px-5 py-3 text-[11px] font-bold uppercase focus:ring-2 focus:ring-blue-100 outline-none text-slate-600 shadow-inner">
                    </div>

                    <button wire:click="create" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-100 transition-all active:scale-95 flex-shrink-0">
                        + USER BARU
                    </button>
                </div>
            </div>
        </div>

        {{-- ALERT MESSAGE --}}
        @if (session()->has('message'))
        <div class="p-4 bg-emerald-50 text-emerald-700 rounded-2xl border border-emerald-100 text-[11px] font-black uppercase tracking-widest animate-in fade-in">
            {{ session('message') }}
        </div>
        @endif

        {{-- TABLE SECTION --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Nama User</th>
                        <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Email Akun</th>
                        <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest text-center w-52 border-none">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-8 py-5 text-sm font-bold text-slate-700 uppercase tracking-tight">{{ $user->name }}</td>
                        <td class="px-8 py-5 text-sm text-slate-400 font-bold lowercase">{{ $user->email }}</td>
                        <td class="px-8 py-5">
                            <div class="flex justify-center items-center gap-3">
                                <button wire:click="edit({{ $user->id }})"
                                    class="px-5 py-2 bg-amber-400 text-white font-black rounded-xl shadow-lg shadow-amber-100 hover:bg-amber-500 transition-all text-[9px] uppercase tracking-widest">
                                    Edit
                                </button>

                                <button onclick="confirm('Hapus akses user ini?') || event.stopImmediatePropagation()"
                                    wire:click="delete({{ $user->id }})"
                                    class="px-5 py-2 bg-rose-500 text-white font-black rounded-xl shadow-lg shadow-rose-100 hover:bg-rose-600 transition-all text-[9px] uppercase tracking-widest">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-8 py-16 text-center text-slate-300 text-[11px] font-black uppercase tracking-[0.2em] opacity-60">Data tidak ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- PAGINATION --}}
            <div class="px-8 py-6 border-t border-slate-50 bg-slate-50/30">
                {{ $users->links() }}
            </div>
        </div>

        {{-- MODAL (Tetap rapi tanpa italic) --}}
        @if($isOpen)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm animate-in fade-in">
            <div class="bg-white rounded-[3rem] shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-8 border-b border-slate-50 flex items-center gap-4">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-black">U</div>
                    <h3 class="text-xl font-black text-slate-800 uppercase tracking-tight">{{ $userId ? 'Update User' : 'User Registration' }}</h3>
                </div>

                <div class="p-8 space-y-6 bg-white">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                        <input wire:model="name" type="text" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-[11px] font-bold uppercase shadow-inner outline-none focus:ring-4 focus:ring-blue-50">
                        @error('name') <span class="text-rose-500 text-[9px] font-bold uppercase mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Email</label>
                        <input wire:model="email" type="email" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-[11px] font-bold lowercase shadow-inner outline-none focus:ring-4 focus:ring-blue-50">
                        @error('email') <span class="text-rose-500 text-[9px] font-bold uppercase mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                            Password {{ $userId ? '(Optional)' : '' }}
                        </label>
                        <input wire:model="password" type="password" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-[11px] font-bold shadow-inner outline-none focus:ring-4 focus:ring-blue-50">
                        @error('password') <span class="text-rose-500 text-[9px] font-bold uppercase mt-2 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="p-8 bg-slate-50 flex justify-end gap-4">
                    <button wire:click="closeModal" class="px-6 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Batal</button>
                    <button wire:click="save" class="px-10 py-3 text-[10px] font-black text-white bg-blue-600 hover:bg-blue-700 rounded-2xl shadow-xl shadow-blue-100 transition-all active:scale-95 uppercase">
                        {{ $userId ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>