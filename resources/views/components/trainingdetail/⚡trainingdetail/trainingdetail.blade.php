<div class="min-h-screen bg-white p-4 lg:p-8 font-sans">
    {{-- NOTIFIKASI MELAYANG --}}
    @if (session()->has('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
        class="fixed top-5 right-5 z-[200] bg-emerald-600 text-white px-8 py-4 rounded-[2rem] shadow-2xl shadow-emerald-200 font-black text-[11px] uppercase tracking-widest animate-in fade-in slide-in-from-top-4">
        ✅ {{ session('status') }}
    </div>
    @endif

    <div class="max-w-7xl mx-auto space-y-8">
        {{-- HEADER SECTION --}}
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Training Detail Report</h2>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">
                        Laporan detail riwayat pelatihan karyawan
                    </p>
                </div>

                <button wire:click="exportExcel" wire:loading.attr="disabled"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-emerald-100 transition-all active:scale-95 text-[10px] tracking-widest uppercase flex items-center gap-3">
                    <span wire:loading wire:target="exportExcel" class="animate-spin text-xs">🌀</span>
                    <svg wire:loading.remove wire:target="exportExcel" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    EXPORT CSV
                </button>
            </div>
        </div>

        {{-- FILTER PANEL --}}
        <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">

                {{-- 1. Nama / NIK --}}
                <div class="space-y-1.5">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Nama / NIK</label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari..."
                        class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-4 focus:ring-blue-50 shadow-inner text-slate-900 placeholder:text-slate-400">
                </div>

                {{-- 2. Judul Training --}}
                <div class="space-y-1.5">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Judul Training</label>
                    <select wire:model.live="title_filter"
                        class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-[10px] font-bold uppercase outline-none focus:ring-4 focus:ring-blue-50 shadow-inner appearance-none transition-all text-slate-900">
                       
                        <option value="" class="text-slate-900 font-bold">SEMUA JUDUL</option>
                        @foreach($allTitles as $t)
                        <option value="{{ $t->title }}" class="text-slate-900">{{ $t->title }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Dari Tanggal --}}
                <div class="space-y-1.5">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Dari Tanggal</label>
                    <input type="date" wire:model.live="date_from"
                        class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-[10px] font-bold outline-none focus:ring-4 focus:ring-blue-50 shadow-inner text-slate-600">
                </div>

                {{-- 4. Sampai Tanggal --}}
                <div class="space-y-1.5">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Sampai Tanggal</label>
                    <input type="date" wire:model.live="date_to"
                        class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-[10px] font-bold outline-none focus:ring-4 focus:ring-blue-50 shadow-inner text-slate-600">
                </div>

                {{-- 5. Tombol Reset --}}
                <div>
                    <button wire:click="resetFilters"
                        class="w-full py-3.5 bg-blue-100 hover:bg-slate-200 text-black-400 font-black rounded-xl text-[9px] uppercase tracking-widest transition-all active:scale-95 border border-slate-200/50 shadow-sm">
                        RESET FILTERS
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl p-6 text-white shadow-lg shadow-blue-200">
                <p class="text-blue-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Kehadiran</p>
                <h3 class="text-4xl font-black">{{ $total_trainings }} <span class="text-lg font-bold text-blue-200">x Training</span></h3>
            </div>
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 text-white shadow-lg shadow-emerald-200">
                <p class="text-emerald-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Durasi Belajar</p>
                <h3 class="text-4xl font-black">{{ $total_hours }} <span class="text-lg font-bold text-emerald-200">Jam</span></h3>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead class="bg-blue-600">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Peserta</th>
                            <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Detail Training</th>
                            <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Trainer</th>
                            <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Jadwal</th>
                            <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none text-center">Score</th>
                            <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none text-center">Status & Fee</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($rows as $row)
                        <tr class="hover:bg-slate-50 transition-colors" wire:key="report-{{ $row->participant_id }}">
                            <td class="px-8 py-5">
                                <div class="font-bold text-slate-700 text-sm uppercase tracking-tight">{{ $row->employee_name }}</div>
                                <div class="mt-1">
                                    <span class="bg-blue-50 text-blue-600 text-[9px] px-2 py-0.5 rounded-lg border border-blue-100 inline-flex items-center font-black">
                                        {{ $row->nik }} <span class="mx-1">•</span> {{ $row->department ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-black text-slate-700 uppercase leading-snug whitespace-normal min-w-[200px]">{{ $row->title }}</div>
                                <div class="flex gap-2 mt-2">
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold border border-slate-100 uppercase bg-slate-50 text-slate-400">{{ $row->held_by }}</span>
                                    <span class="px-2 py-0.5 rounded-lg text-[9px] font-bold border border-blue-100 uppercase bg-blue-50 text-blue-600">{{ $row->activity_name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <div class="font-bold text-slate-700 text-xs uppercase">{{ $row->trainer_internal_name ?? ($row->trainer_external_name ?? '-') }}</div>
                                <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded-lg border {{ $row->trainer_internal_name ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-sky-600 bg-sky-50 border-sky-100' }}">
                                    {{ $row->trainer_internal_name ? 'Internal' : 'External' }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-bold text-slate-600 uppercase tracking-tight">📅 {{ \Carbon\Carbon::parse($row->training_date)->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400 mt-1 font-bold italic uppercase">⏰ {{ \Carbon\Carbon::parse($row->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($row->finish_time)->format('H:i') }}</div>
                            </td>
                            <td class="px-8 py-5 text-center">
                                <input type="number"
                                    wire:change="updateScore({{ $row->participant_id }}, $event.target.value)"
                                    value="{{ $row->score }}"
                                    class="w-20 px-3 py-2 bg-slate-50 border border-slate-100 rounded-xl text-sm font-black text-center outline-none focus:ring-4 focus:ring-blue-50 transition-all text-blue-600"
                                    placeholder="0">
                            </td>
                            <td class="px-8 py-5 text-center">
                                <div class="font-black text-slate-700 text-sm italic">Rp{{ number_format($row->fee, 0, ',', '.') }}</div>
                                <div class="mt-2">
                                    <span class="px-3 py-1 rounded-xl text-[9px] font-black border uppercase {{ $row->is_certified == 'Yes' ? 'bg-emerald-500 text-white border-emerald-500 shadow-lg shadow-emerald-100' : 'bg-slate-100 text-slate-400 border-slate-200' }}">
                                        {{ $row->is_certified == 'Yes' ? 'Certified ✓' : 'No Cert' }}
                                    </span>
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

            {{-- Pagination Section --}}
            <div class="bg-slate-50/50 px-8 py-6 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-6">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        Showing {{ $rows->firstItem() }} - {{ $rows->lastItem() }} of {{ $rows->total() }} Records
                    </div>
                    <div class="flex items-center gap-3">
                        <button wire:click="previousPage" @disabled($rows->onFirstPage()) class="px-6 py-3 bg-white text-blue-600 hover:bg-blue-600 hover:text-white disabled:opacity-30 rounded-2xl text-[10px] font-black uppercase border border-slate-100 transition-all shadow-sm active:scale-95">PREV</button>
                        <div class="bg-blue-600 text-white px-5 py-3 rounded-2xl text-[10px] font-black shadow-lg shadow-blue-200">{{ $rows->currentPage() }}</div>
                        <button wire:click="nextPage" @disabled(!$rows->hasMorePages()) class="px-6 py-3 bg-white text-blue-600 hover:bg-blue-600 hover:text-white disabled:opacity-30 rounded-2xl text-[10px] font-black uppercase border border-slate-100 transition-all shadow-sm active:scale-95">NEXT</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>