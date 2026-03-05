<div class="min-h-screen bg-white p-4 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">

        {{-- HEADER SECTION: KOTAK JUDUL (Seragam) --}}
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Trainer Contribution Report</h2>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">
                        Total jam mengajar trainer berdasarkan kategori
                    </p>
                </div>

                <button wire:click="exportExcel" wire:loading.attr="disabled"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-emerald-100 transition-all active:scale-95 text-[10px] tracking-widest uppercase">
                    <span wire:loading wire:target="exportExcel" class="animate-spin text-xs">🌀</span>
                    EXPORT EXCEL
                </button>
            </div>
        </div>

        {{-- FILTER PANEL --}}
        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
        
        {{-- Cari Trainer (Sekarang Jadi Dropdown) --}}
        <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Pilih Trainer</label>
            <select wire:model.live="search"
                class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-[11px] font-bold uppercase outline-none focus:ring-4 focus:ring-blue-50 shadow-inner appearance-none transition-all
                {{ !$search ? 'text-slate-300' : 'text-slate-600' }}">
                
                <option value="">SEMUA TRAINER</option>
                @foreach($trainerList as $t)
                    <option value="{{ $t->name }}" class="text-slate-600">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Dari Tanggal</label>
            <input type="date" wire:model.live="date_from"
                class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-[11px] font-bold outline-none focus:ring-4 focus:ring-blue-50 shadow-inner text-slate-600">
        </div>

        <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sampai Tanggal</label>
            <input type="date" wire:model.live="date_to"
                class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-[11px] font-bold outline-none focus:ring-4 focus:ring-blue-50 shadow-inner text-slate-600">
        </div>

        <button wire:click="resetFilters"
            class="px-8 py-4 bg-blue-100 hover:bg-slate-200 text-black-400 font-black rounded-2xl text-[10px] uppercase tracking-widest transition-all">
            Reset Filter
        </button>
    </div>
</div>

            {{-- TABLE SECTION --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none text-center w-16">No</th>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Nama Trainer</th>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Organization</th>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Activities</th>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Skill</th>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none text-center">Total Mengajar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse ($contributions as $index => $item)
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-8 py-5 text-center font-black text-slate-300 text-xs">
                                    {{ $contributions->firstItem() + $index }}
                                </td>
                                <td class="px-8 py-5 font-bold text-slate-700 uppercase text-xs tracking-tight">
                                    {{ $item->trainer_name ?: 'TANPA NAMA' }}
                                </td>
                                <td class="px-8 py-5 font-bold text-slate-400 uppercase text-[11px] tracking-tight">
                                    {{ $item->organization }}
                                </td>
                                <td class="px-8 py-5 text-[11px] text-slate-500 font-black uppercase">
                                    {{ $item->activity_name ?: '-' }}
                                </td>
                                <td class="px-8 py-5 text-[11px] text-slate-500 font-black uppercase">
                                    {{ $item->skill_name ?: '-' }}
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="inline-flex items-center justify-center px-4 py-1.5 text-[11px] font-black text-blue-600 bg-blue-50 rounded-xl border border-blue-100 shadow-sm uppercase tracking-tighter">
                                        {{ round($item->total_minutes / 60, 0) }} JAM
                                    </span>

                                    {{-- Tombol Lihat Detail --}}
        <button wire:click="showDetail('{{ $item->trainer_name }}')" 
            class="p-2 bg-white text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-xl border border-emerald-100 shadow-sm transition-all active:scale-95 group">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
        </button>
    </div>
</td>
                                
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-30">
                                        <h3 class="text-slate-800 font-black uppercase text-[11px] tracking-widest">Data Tidak Ditemukan</h3>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="bg-slate-50/30 border-t border-slate-50 px-8 py-6 flex items-center justify-between">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Showing data {{ $contributions->firstItem() ?? 0 }}
                    </div>

                    <div class="flex gap-2">
                        <button wire:click="previousPage" @disabled($contributions->onFirstPage())
                            class="px-6 py-3 bg-white border border-slate-100 rounded-2xl text-[10px] font-black text-blue-600 hover:bg-blue-600 hover:text-white disabled:opacity-30 transition-all shadow-sm active:scale-95">
                            PREV
                        </button>

                        <button wire:click="nextPage" @disabled(!$contributions->hasMorePages())
                            class="px-6 py-3 bg-blue-600 border border-blue-600 rounded-2xl text-[10px] font-black text-white hover:bg-blue-700 disabled:opacity-30 transition-all shadow-lg shadow-blue-100 active:scale-95">
                            NEXT
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🔥 MODAL DETAIL TRAINING (RAMPING & SCROLLABLE) --}}
@if($showDetailModal)
<div class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-md">
        <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
        
        {{-- Header Modal --}}
        <div class="p-8 border-b border-slate-50 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight italic">Detail Mengajar</h3>
                <p class="text-[10px] text-blue-500 font-black uppercase tracking-widest mt-1 italic">
                    {{ $selectedTrainerName }}
                </p>
            </div>
            <button wire:click="$set('showDetailModal', false)" class="h-10 w-10 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all font-bold shadow-inner">✕</button>
        </div>

        
        <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar bg-slate-50/30">
            <div class="space-y-3">
                @forelse($trainerDetails as $detail)
                <div class="p-4 bg-white rounded-[1.5rem] border border-slate-100 shadow-sm hover:border-blue-100 transition-all group">
                    <div class="flex justify-between items-start">
                        <div class="space-y-1">
                            <p class="text-[11px] font-black text-slate-700 uppercase tracking-tight leading-tight group-hover:text-blue-600 transition-colors">
                                {{ $detail->title }}
                            </p>
                            <div class="flex items-center gap-2 text-[9px] text-slate-400 font-bold uppercase italic">
                                <span>📅 {{ \Carbon\Carbon::parse($detail->training_date)->format('d/m/y') }}</span>
                                <span class="text-slate-200">•</span>
                                <span>⏰ {{ \Carbon\Carbon::parse($detail->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($detail->finish_time)->format('H:i') }}</span>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-xl font-black text-[10px] border border-emerald-100 uppercase">
                                {{ round($detail->minutes / 60, 1) }}H
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <p class="py-10 text-center text-slate-400 font-bold uppercase text-[10px] tracking-widest opacity-50">Belum ada data mengajar.</p>
                @endforelse
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="p-6 bg-slate-50/50">
            <button wire:click="$set('showDetailModal', false)" 
                class="w-full py-4 bg-white border border-slate-200 text-slate-400 font-black rounded-2xl text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all shadow-sm active:scale-95">
                CLOSE
            </button>
        </div>
    </div>
</div>
@endif
    </div>