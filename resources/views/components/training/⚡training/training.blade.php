<div class="flex flex-col gap-8 p-8 bg-white min-h-screen font-sans text-slate-900">

    {{-- HEADER SECTION: KOTAK JUDUL PUTIH BERSIH --}}
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">
                    Training Data Management
                </h2>
                <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">
                    Training Management System
            </div>

            <div class="flex flex-wrap gap-3">
                <button wire:click="$set('show_import_modal', true)"
                    class="px-6 py-3 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all text-[10px] uppercase tracking-widest italic">
                    IMPORT EXCEL
                </button>

                <button wire:click="openCreateModal"
                    class="px-6 py-3 bg-emerald-600 text-white font-black rounded-2xl hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all text-[10px] uppercase tracking-widest italic">
                    TAMBAH TRAINING
                </button>
            </div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        {{-- SIDEBAR KIRI: LIST DATA --}}
        <aside class="flex-1 flex flex-col h-[calc(100vh-64px)] sticky top-8 bg-white rounded-[2.5rem] border border-slate-200 shadow-xl overflow-hidden transition-all">

            {{-- HEADER SIDEBAR (Sekarang hanya untuk Search) --}}
            <div class="p-6 bg-white border-b border-slate-100 space-y-4">
                <div class="flex justify-between items-center gap-4">
                    <div class="flex-1 relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="sidebar_search" placeholder="CARI JUDUL TRAINING..." class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 rounded-2xl text-[11px] font-black uppercase text-slate-700">
                    </div>
                </div>
            </div>

            {{-- LIST DATA --}}
            <div class="flex-1 overflow-y-auto divide-y divide-slate-50 custom-scrollbar">
                @forelse($trainings as $t)
                <div class="relative group w-full transition-all {{ $training_id == $t->id ? 'bg-blue-50 border-l-4 border-blue-600' : 'hover:bg-blue-50/50' }}">
                    <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-all z-20">
                        <button wire:click="loadTraining({{ $t->id }})" class="p-2 bg-white text-blue-600 rounded-xl shadow-sm hover:bg-blue-600 hover:text-white border border-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                        <button wire:click="deleteTraining({{ $t->id }})" wire:confirm="Hapus data training ini?" class="p-2 bg-white text-rose-500 rounded-xl shadow-sm hover:bg-rose-500 hover:text-white border border-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </div>

                    <div class="w-full text-left p-6">
                        <span class="text-[9px] font-black {{ $training_id == $t->id ? 'text-blue-600' : 'text-slate-400' }} uppercase tracking-widest block mb-2 italic">
                            {{ \Carbon\Carbon::parse($t->training_date)->format('Y-m-d') }}
                        </span>
                        <h5 class="font-extrabold text-sm text-slate-800 leading-snug uppercase pr-12">{{ $t->title }}</h5>
                        <div class="flex flex-wrap items-center gap-2 mt-4">
                            <span class="text-[9px] bg-slate-100 px-3 py-1 rounded-lg text-slate-500 font-bold uppercase border border-slate-200 tracking-tighter">{{ $t->held_by }}</span>
                            <span class="text-[9px] bg-blue-100 px-3 py-1 rounded-lg text-blue-600 font-bold uppercase border border-blue-200 tracking-tighter">{{ $t->activity_name }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-20 text-center opacity-30 flex flex-col items-center">
                    <span class="text-4xl mb-4 italic font-black">?</span>
                    <p class="text-[10px] font-black uppercase italic">Data Training Kosong</p>
                </div>
                @endforelse
            </div>

            {{-- PAGINATION --}}
            <div class="p-6 bg-slate-50 border-t border-slate-100">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                        Showing <span class="text-blue-600">{{ $trainings->firstItem() }}</span>
                        to <span class="text-blue-600">{{ $trainings->lastItem() }}</span>
                        of <span class="text-blue-600">{{ $trainings->total() }}</span> Results
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($trainings->onFirstPage())
                        <span class="w-10 h-10 flex items-center justify-center bg-white text-slate-200 rounded-2xl border border-slate-100 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        @else
                        <button wire:click="previousPage" class="w-10 h-10 flex items-center justify-center bg-white text-slate-600 rounded-2xl border border-slate-200 hover:bg-blue-600 hover:text-white transition-all shadow-sm active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        @endif

                        <div class="px-5 py-2 bg-blue-50 text-blue-700 rounded-2xl text-[10px] font-black border border-blue-100 uppercase italic">
                            Page {{ $trainings->currentPage() }} / {{ $trainings->lastPage() }}
                        </div>

                        @if ($trainings->hasMorePages())
                        <button wire:click="nextPage" class="w-10 h-10 flex items-center justify-center bg-white text-slate-600 rounded-2xl border border-slate-200 hover:bg-blue-600 hover:text-white transition-all shadow-sm active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        @else
                        <span class="w-10 h-10 flex items-center justify-center bg-white text-slate-200 rounded-2xl border border-slate-100 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </aside>

        {{-- MODAL FORM --}}
        @if($showFormModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/70 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white w-full max-w-6xl rounded-[3rem] shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-300">

                {{-- HEADER MODAL --}}
                <div class="p-8 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-blue-200 text-2xl font-bold italic underline">T</div>
                        <div>
                            <h3 class="font-black text-2xl text-slate-800 tracking-tighter uppercase italic">{{ $training_id ? 'Update Data Training' : 'Input Training Baru' }}</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em] italic">Human Capital System • Jembo Training</p>
                        </div>
                    </div>
                    <button wire:click="$set('showFormModal', false)" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all font-bold shadow-inner">✕</button>
                </div>

                <form wire:submit.prevent="save" class="flex flex-col flex-1 overflow-hidden">
                    {{-- BODY MODAL --}}
                    <div class="p-10 grid grid-cols-1 lg:grid-cols-12 gap-12 overflow-y-auto custom-scrollbar">

                        {{-- SISI KIRI DETAIL (7/12) --}}
                        <div class="lg:col-span-7 space-y-8">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 mb-2 tracking-widest uppercase italic">Training Title</label>
                                <input type="text" wire:model="title" class="w-full bg-slate-50 border-none rounded-2xl p-4 focus:ring-4 focus:ring-blue-100 transition-all text-sm font-bold uppercase italic shadow-inner">
                            </div>

                            {{-- Kolom 2: Trainer (Disederhanakan) --}}
<div class="space-y-2">
    <div class="flex items-center justify-between">
        <label class="block text-[10px] font-black text-slate-400 tracking-widest uppercase italic">Trainer</label>
        <div class="flex bg-slate-100 p-1 rounded-xl gap-1">
            <button type="button" 
                wire:click="$set('trainer_type', 'internal')" 
                class="px-3 py-1 text-[8px] font-black uppercase rounded-lg transition-all {{ $trainer_type === 'internal' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                Internal
            </button>
            <button type="button" 
                wire:click="$set('trainer_type', 'external')" 
                class="px-3 py-1 text-[8px] font-black uppercase rounded-lg transition-all {{ $trainer_type === 'external' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                External
            </button>
        </div>
    </div>

                                @if($trainer_type === 'internal')
                                <select wire:model="trainer_employee_id" class="w-full bg-blue-50/50 border border-blue-100 rounded-2xl p-4 text-[11px] font-bold uppercase italic shadow-sm outline-none focus:ring-2 focus:ring-blue-100">
                                    <option value="">-- PILIH --</option>
                                    @foreach($employees_list as $emp)
                                    <option value="{{ $emp->nik }} - {{ $emp->name }}">{{ strtoupper($emp->name) }}</option>
                                    @endforeach
                                </select>
                                @else
                                <input type="text" wire:model="trainer_external_name" placeholder="NAMA TRAINER..."
                                    class="w-full bg-emerald-50/50 border border-emerald-100 rounded-2xl p-4 text-[11px] font-bold uppercase italic shadow-sm outline-none focus:ring-2 focus:ring-emerald-100">
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 mb-2 tracking-widest uppercase italic">Held By</label>
                                    <input type="text" wire:model="held_by" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold uppercase italic shadow-inner">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 mb-2 tracking-widest uppercase italic">Certification</label>
                                    <select wire:model="is_certified" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold uppercase italic shadow-inner cursor-pointer">
                                        <option value="No">NO (TANPA SERTIFIKAT)</option>
                                        <option value="Yes">YES (ADA SERTIFIKAT)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                {{-- Kolom 1: Activities --}}
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 mb-2 tracking-widest uppercase italic">Activities</label>
                                    <select wire:model="activity_name" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold uppercase italic shadow-inner outline-none focus:ring-2 focus:ring-blue-100">
                                        <option value="">PILIH</option>
                                        <option value="External">EXTERNAL</option>
                                        <option value="Internal">INTERNAL</option>
                                    </select>
                                </div>



                                {{-- Kolom 3: Skill Type --}}
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 mb-2 tracking-widest uppercase italic">Skill Type</label>
                                    <select wire:model="skill_name" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold uppercase italic shadow-inner outline-none focus:ring-2 focus:ring-blue-100">
                                        <option value="">PILIH</option>
                                        <option value="Hard Skill">HARD SKILL</option>
                                        <option value="Soft Skill">SOFT SKILL</option>
                                    </select>
                                </div>
                            </div>

                            <div class="p-8 bg-slate-50/50 rounded-[2.5rem] border border-dashed border-slate-200 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase italic tracking-widest">Training Date</label>
                                        <input type="date" wire:model="training_date" class="w-full bg-white border-none rounded-2xl p-4 text-sm font-bold shadow-sm focus:ring-4 focus:ring-blue-100">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-emerald-500 mb-2 uppercase italic tracking-widest">Start Time</label>
                                        <input type="time" wire:model="start_time" class="w-full bg-white border-none rounded-2xl p-4 text-sm font-bold shadow-sm italic">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-rose-500 mb-2 uppercase italic tracking-widest">Finish Time</label>
                                        <input type="time" wire:model="finish_time" class="w-full bg-white border-none rounded-2xl p-4 text-sm font-bold shadow-sm italic">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 mb-2 uppercase italic tracking-widest">Fee / Cost (RP)</label>
                                    <input type="number" wire:model="fee" class="w-full bg-white border-none rounded-2xl p-4 text-sm font-black text-blue-600 shadow-sm italic">
                                </div>
                            </div>
                        </div>

                        {{-- SISI KANAN PESERTA (5/12) --}}
                        <div class="lg:col-span-5 flex flex-col">
                            <div class="bg-emerald-50 rounded-[2.5rem] border border-emerald-100 p-6 flex flex-col shadow-lg shadow-emerald-100/20 max-h-[500px]">

                                {{-- Header Peserta --}}
                                <div class="flex items-center justify-between mb-4 px-2">
                                    <div>
                                        <h4 class="font-black text-sm text-emerald-900 uppercase italic tracking-tighter">Peserta</h4>
                                        <p class="text-[9px] text-emerald-600 font-bold uppercase italic">{{ count($selected_participants) }} Terpilih</p>
                                    </div>

                                    {{-- Search Box --}}
                                    <div class="relative w-36">
                                        <input type="text" wire:model.live.debounce.300ms="search_participant" placeholder="CARI..."
                                            class="w-full pl-8 pr-3 py-2 bg-white border-none rounded-xl text-[9px] font-bold uppercase shadow-sm focus:ring-2 focus:ring-emerald-400 italic">
                                        <span class="absolute left-2.5 top-2 text-emerald-300 text-xs">🔍</span>

                                        @if(count($this->filteredEmployees) > 0)
                                        <div class="absolute z-[120] w-64 right-0 bg-white mt-2 rounded-xl shadow-2xl border border-emerald-100 overflow-hidden ring-4 ring-emerald-50">
                                            @foreach($this->filteredEmployees as $emp)
                                            <button type="button" wire:click="addSelectedParticipant({{ $emp->id }})"
                                                class="w-full text-left p-3 hover:bg-emerald-50 flex justify-between items-center transition-colors border-b border-emerald-50 last:border-none group">
                                                <div>
                                                    <div class="font-bold text-slate-700 text-[9px] uppercase">{{ $emp->name }}</div>
                                                    <div class="text-[8px] text-slate-400 font-mono">{{ $emp->nik }}</div>
                                                </div>
                                                <span class="text-emerald-500 font-black text-[8px]">+</span>
                                            </button>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="bg-white rounded-[1.5rem] border border-emerald-100 shadow-inner overflow-hidden flex-1">
                                    <div class="max-h-96 overflow-y-auto custom-scrollbar">
                                        <table class="w-full text-left text-[10px]">
                                            <thead class="bg-indigo-600 text-white font-bold uppercase sticky top-0 z-10 italic">
                                                <tr>
                                                    <th class="px-4 py-2.5">NAMA KARYAWAN</th>
                                                    <th class="px-4 py-2.5 text-center">AKSI</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-emerald-50">
                                                @forelse($selected_participants as $p)
                                                <tr class="hover:bg-emerald-50/60 transition-colors uppercase italic">
                                                    <td class="px-5 py-2">
                                                        <div class="font-black text-slate-700 text-[9px] leading-tight">{{ $p['name'] }}</div>
                                                        <div class="text-[7px] text-slate-400 font-medium italic uppercase tracking-tighter">
                                                            {{ $p['org_name'] ?? 'DEPARTEMEN TIDAK DITEMUKAN' }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-2 text-center">
                                                        <button type="button" wire:click="removeParticipant({{ $p['id'] }})" class="w-6 h-6 rounded-lg bg-rose-100 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center mx-auto shadow-sm">✕</button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="2" class="p-10 text-center text-emerald-800 font-black opacity-30 italic uppercase">Kosong</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL FOOTER - Tombol Berada di Dalam Modal --}}
                    <div class="p-8 bg-slate-50 border-t border-slate-100 flex justify-end gap-4 shrink-0">
                        <button type="button" wire:click="$set('showFormModal', false)" class="px-8 py-4 rounded-2xl font-black text-slate-400 hover:bg-slate-200 uppercase text-[10px] tracking-widest transition-all italic">Batal</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-12 py-4 rounded-2xl font-black shadow-lg shadow-blue-100 transition-all active:scale-95 uppercase italic tracking-tight">
                            {{ $training_id ? 'Simpan Perubahan' : 'Publish Training Baru' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- MODAL IMPORT EXCEL --}}
        @if($show_import_modal)
        <div class="fixed inset-0 z-[110] flex items-center justify-center p-6 bg-slate-900/70 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white w-full max-w-md rounded-[3rem] shadow-2xl border border-slate-200 overflow-hidden animate-in zoom-in-95 duration-300 flex flex-col">

                {{-- Header Modal: Putih Bersih sesuai instruksi mentor --}}
                <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-white text-slate-800">
                    <div>
                        <h3 class="font-black uppercase italic text-sm tracking-tighter">Import Data Training</h3>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Format: .xlsx / .xls</p>
                    </div>
                    <button wire:click="$set('show_import_modal', false)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:text-rose-500 transition-all font-bold shadow-inner">✕</button>
                </div>

                <form wire:submit.prevent="importExcel" class="p-8 space-y-6">
                    {{-- Input File Custom Style --}}
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 mb-2 tracking-widest uppercase italic">Pilih File Excel</label>
                        <input type="file" wire:model="excel_file"
                            class="w-full text-[11px] font-bold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer italic bg-slate-50 p-4 rounded-2xl shadow-inner border-none">
                        @error('excel_file') <span class="text-rose-500 text-[10px] font-bold uppercase italic">{{ $message }}</span> @enderror
                    </div>

                    {{-- Tombol dengan Teks Sesuai Permintaan --}}
                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-2xl font-black uppercase text-[11px] shadow-lg shadow-emerald-100 transition-all active:scale-95 flex justify-center items-center gap-2 italic">
                        <span wire:loading wire:target="excel_file" class="animate-spin text-xs">🌀</span>
                        START IMPORT DATA
                    </button>
                </form>

                {{-- Footer Modal Simpel --}}
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-center italic">
                    <button type="button" wire:click="$set('show_import_modal', false)" class="text-[9px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-all">Batal & Tutup</button>
                </div>
            </div>
        </div>
        @endif
        <style>
            .custom-scrollbar::-webkit-scrollbar {
                width: 5px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #e2e8f0;
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #cbd5e1;
            }
        </style>
    </div>