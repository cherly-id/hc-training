<div class="min-h-screen bg-white p-4 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        {{-- HEADER SECTION: KOTAK JUDUL (Seragam) --}}
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Atribut Management</h2>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">
                        Kelola Kategori Aktivitas dan Tipe Skill
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input type="text" wire:model.live="search" placeholder="Cari data..."
                            class="px-5 py-3 bg-slate-50 border-none rounded-2xl text-[11px] font-bold uppercase focus:ring-2 focus:ring-blue-100 outline-none w-64 shadow-inner text-slate-600">
                    </div>

                    <button wire:click="$set('is_adding', true)"
                        class="px-6 py-3 bg-blue-600 text-white rounded-2xl hover:bg-blue-700 transition-all font-black text-[10px] uppercase tracking-widest shadow-lg shadow-blue-100 active:scale-95">
                        ADD NEW
                    </button>
                </div>
            </div>
        </div>

        {{-- AREA CRUD DYNAMIS --}}
        <div class="space-y-6">
            {{-- Notifikasi Sukses --}}
            @if (session()->has('msg'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-between animate-in fade-in">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-black uppercase tracking-widest">{{ session('msg') }}</span>
                    </div>
                    <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">×</button>
                </div>
            @endif

            {{-- Form Input --}}
            @if($is_adding || $selected_id)
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden transition-all">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-600"></div>
                    
                    <div class="flex flex-col md:flex-row gap-6 items-end">
                        <div class="flex-1 w-full">
                            <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase tracking-widest">
                                Nama {{ $tab === 'activities' ? 'Activity' : 'Skill' }}
                            </label>
                            <input type="text" 
                                wire:model="new_name" 
                                placeholder="Ketik nama di sini..."
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-sm text-slate-700 uppercase">
                            @error('new_name') 
                                <span class="text-[10px] text-rose-500 font-black uppercase mt-2 ml-1 block">{{ $message }}</span> 
                            @enderror
                        </div>
                        
                        <div class="flex gap-2 w-full md:w-auto">
                            <button wire:click="save" 
                                class="flex-1 md:flex-none px-10 py-4 bg-blue-600 text-white rounded-2xl font-black text-[10px] hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 uppercase tracking-widest active:scale-95">
                                {{ $selected_id ? 'Update' : 'Simpan' }}
                            </button>
                            <button wire:click="cancel" 
                                class="px-8 py-4 bg-slate-100 text-slate-400 rounded-2xl font-black text-[10px] hover:bg-slate-200 transition-all uppercase tracking-widest">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Tab Navigasi --}}
        <div class="flex gap-8 mb-2 px-2 border-b border-slate-100">
            <button wire:click="$set('tab', 'activities')"
                class="pb-4 px-2 text-[11px] font-black tracking-[0.2em] transition-all {{ $tab == 'activities' ? 'text-blue-600 border-b-4 border-blue-600' : 'text-slate-300 hover:text-slate-500' }}">
                ACTIVITIES
            </button>
            <button wire:click="$set('tab', 'skills')"
                class="pb-4 px-2 text-[11px] font-black tracking-[0.2em] transition-all {{ $tab == 'skills' ? 'text-blue-600 border-b-4 border-blue-600' : 'text-slate-300 hover:text-slate-500' }}">
                SKILLS
            </button>
        </div>

        {{-- Tabel Utama --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest w-32 border-none">ID</th>
                        <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">
                            Nama {{ $tab === 'activities' ? 'Activities' : 'Skills' }}
                        </th>
                        <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest text-center w-64 border-none">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($this->items as $item)
                        <tr class="hover:bg-slate-50 transition-colors group" wire:key="item-{{ $item->id }}">
                            <td class="px-8 py-5 text-blue-300 font-mono text-xs font-bold">#{{ $item->id }}</td>
                            <td class="px-8 py-5 font-bold text-slate-700 uppercase tracking-tight">{{ $item->name }}</td>
                            <td class="px-8 py-5">
                                <div class="flex justify-center items-center gap-3">
                                    <button wire:click="edit({{ $item->id }}, '{{ $item->name }}')"
                                        class="px-5 py-2 bg-amber-400 text-white text-[9px] font-black rounded-xl hover:bg-amber-500 transition-all shadow-md shadow-amber-100 uppercase tracking-widest">
                                        EDIT
                                    </button>
                                    <button onclick="confirm('Hapus data ini?') || event.stopImmediatePropagation()"
                                        wire:click="delete({{ $item->id }})"
                                        class="px-5 py-2 bg-rose-500 text-white text-[9px] font-black rounded-xl hover:bg-rose-600 transition-all shadow-md shadow-rose-100 uppercase tracking-widest">
                                        DELETE
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-8 py-20 text-center text-slate-300 text-[11px] font-black uppercase tracking-[0.2em] opacity-60">
                                Tidak ada data {{ $tab }} ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>