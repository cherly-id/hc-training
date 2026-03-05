<div class="min-h-screen bg-white p-4 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">

        {{-- HEADER SECTION: KOTAK JUDUL (Seragam) --}}
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Management Master Data</h2>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">Training Management System</p>
                </div>

                {{-- Tab Switcher: Dibuat lebih estetik --}}
                <div class="inline-flex bg-slate-50 p-1.5 rounded-2xl border border-slate-100 shadow-inner gap-2">
                    @foreach(['org' => 'Organization', 'pos' => 'Position'] as $key => $label)
                    <button
                        type="button"
                        wire:click="$set('activeTab', '{{ $key }}')"
                        class="px-6 py-2.5 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-300 {{ $activeTab === $key ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-400 hover:text-indigo-500' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-sm animate-in fade-in">
            {{ session('success') }}
        </div>
        @endif

        {{-- FORM SECTION --}}
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1.5 h-full bg-indigo-600"></div>

            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">
                {{ $editingId ? 'Update Data ' . ($activeTab == 'org' ? 'Organization' : 'Position') : 'Tambah Data ' . ($activeTab == 'org' ? 'Organization' : 'Position') }}
            </h4>

            <form wire:submit="save">
                <div class="flex flex-col md:flex-row items-end gap-4">
                    <div class="flex-1 space-y-2 w-full">
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">
                            {{ $activeTab == 'org' ? 'Nama Departemen' : 'Nama Jabatan' }}
                        </label>
                        <input type="text" wire:model="name" class="w-full px-5 py-3.5 rounded-2xl bg-slate-50 border-none focus:ring-4 focus:ring-indigo-100 outline-none transition-all text-sm font-bold uppercase" placeholder="Ketik nama data master...">
                        @error('name') <span class="text-rose-500 text-[9px] font-black uppercase ml-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-2 w-full md:w-auto">
                        <button type="submit" class="flex-1 md:flex-none px-10 py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all flex justify-center items-center gap-2 text-[10px] uppercase tracking-widest active:scale-95">
                            {{ $editingId ? 'Update Data' : 'Simpan Data' }}
                        </button>

                        @if($editingId)
                        <button type="button" wire:click="resetForm" class="px-6 py-4 bg-slate-100 text-slate-400 font-black rounded-2xl hover:bg-slate-200 transition-all text-[10px] uppercase tracking-widest">
                            Batal
                        </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- SEARCH AREA --}}
        <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 flex items-center w-fit gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari data master..." class="pl-4 pr-4 py-2 bg-transparent border-none focus:ring-0 text-sm font-bold uppercase text-slate-600">
            <button wire:click="$set('search', '')" class="px-4 py-2 bg-slate-50 text-slate-400 font-black rounded-xl text-[10px] uppercase hover:text-slate-600 transition-colors">Reset</button>
        </div>

        {{-- TABLE SECTION --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-blue-600">
                        <tr>
                            <th class="px-8 py-5 w-24 text-[10px] font-black text-white uppercase tracking-widest border-none">No</th>
                            <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Nama Item Master</th>
                            <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest text-center w-64 border-none">Aksi Pengelolaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($this->masterData as $index => $row)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-8 py-5 text-xs font-bold text-slate-300 group-hover:text-indigo-400">
                                {{ $this->masterData->firstItem() + $index }}
                            </td>

                            <td class="px-8 py-5">
                                <span class="text-sm font-bold text-slate-700 uppercase tracking-tight">
                                    {{ $activeTab === 'org' ? $row->org_name : $row->position_name }}
                                </span>
                            </td>

                            <td class="px-8 py-5">
                                <div class="flex justify-center items-center gap-3">
                                    <button wire:click="edit({{ $row->id }})"
                                        class="px-5 py-2 bg-amber-400 text-white font-black rounded-xl shadow-lg shadow-amber-100 hover:bg-amber-500 transition-all text-[9px] uppercase tracking-widest">
                                        Edit
                                    </button>

                                    <button onclick="confirm('Yakin ingin menghapus?') || event.stopImmediatePropagation()"
                                        wire:click="delete({{ $row->id }})"
                                        class="px-5 py-2 bg-rose-500 text-white font-black rounded-xl shadow-lg shadow-rose-100 hover:bg-rose-600 transition-all text-[9px] uppercase tracking-widest">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-8 py-16 text-center text-slate-300 text-[11px] font-black uppercase tracking-[0.2em] opacity-60">
                                Data tidak ditemukan...
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="px-10 py-8 bg-slate-50/50 border-t border-slate-50 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em]">
                    SHOWING <span class="text-indigo-600">{{ $this->masterData->firstItem() ?? 0 }}</span> TO <span class="text-indigo-600">{{ $this->masterData->lastItem() ?? 0 }}</span> OF <span class="text-slate-800">{{ $this->masterData->total() }}</span> MASTER ITEMS
                </div>

                <div class="flex items-center gap-3">
                    {{-- Custom Pagination Manual --}}
                    @if ($this->masterData->onFirstPage())
                    <span class="px-8 py-3 bg-white text-slate-200 font-[1000] text-[10px] uppercase tracking-widest rounded-2xl border border-slate-100 cursor-not-allowed shadow-inner">PREV</span>
                    @else
                    <button wire:click="previousPage" class="px-8 py-3 bg-white text-indigo-600 font-[1000] text-[10px] uppercase tracking-widest rounded-2xl shadow-lg shadow-indigo-100/50 border border-slate-100 hover:bg-indigo-600 hover:text-white transition-all active:scale-95 italic">PREV</button>
                    @endif

                    <div class="px-5 py-3 bg-indigo-50 rounded-2xl text-[11px] font-black text-indigo-600 uppercase tracking-widest shadow-inner border border-indigo-100">
                        PAGE {{ $this->masterData->currentPage() }}
                    </div>

                    @if ($this->masterData->hasMorePages())
                    <button wire:click="nextPage" class="px-8 py-3 bg-white text-indigo-600 font-[1000] text-[10px] uppercase tracking-widest rounded-2xl shadow-lg shadow-indigo-100/50 border border-slate-100 hover:bg-indigo-600 hover:text-white transition-all active:scale-95 italic">NEXT</button>
                    @else
                    <span class="px-8 py-3 bg-white text-slate-200 font-[1000] text-[10px] uppercase tracking-widest rounded-2xl border border-slate-100 cursor-not-allowed shadow-inner">NEXT</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@script
<script>
    $wire.on('swal:success', (data) => {
        Swal.fire({
            title: 'BERHASIL!',
            text: data.message,
            icon: 'success',
            iconColor: '#10b981',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#ffffff',
            padding: '3rem',
            customClass: {
                popup: 'rounded-[3rem] border-none shadow-2xl',
                title: 'font-[1000] uppercase tracking-tighter text-3xl italic text-slate-800',
                htmlContainer: 'font-black uppercase text-[11px] tracking-[0.2em] text-slate-400 mt-4'
            }
        });
    });
</script>
@endscript