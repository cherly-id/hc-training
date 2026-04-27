<div> {{-- Pembungkus Utama LIVEWIRE --}}
    <div class="min-h-screen bg-white p-4 lg:p-8 font-sans">
        <div class="max-w-7xl mx-auto space-y-8">

            {{-- HEADER SECTION --}}
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Training Penetration Report</h2>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">
                            Persentase jangkauan pelatihan per departemen
                        </p>
                    </div>

                    <button wire:click="exportExcel" wire:loading.attr="disabled"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-emerald-100 transition-all active:scale-95 text-[10px] tracking-widest uppercase flex items-center gap-2">
                        <span wire:loading wire:target="exportExcel" class="animate-spin text-xs">🌀</span>
                        <svg wire:loading.remove wire:target="exportExcel" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        EXPORT EXCEL
                    </button>
                </div>
            </div>

            {{-- FILTER PANEL (DIPERSIKAT & SEJAJAR) --}}
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm">
                {{-- lg:grid-cols-5 memastikan semua input sejajar sebaris di layar komputer --}}
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">

                    {{-- 1. Pilih Departemen --}}
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Departemen</label>
                        <select wire:model.live="search"
                            class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-[11px] font-bold uppercase outline-none focus:ring-4 focus:ring-blue-50 shadow-inner text-slate-600 cursor-pointer appearance-none">
                            <option value="">-- SEMUA DEPT --</option>
                            @foreach($allOrganizations as $org)
                            <option value="{{ $org->org_name }}">{{ $org->org_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Mulai --}}
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Mulai</label>
                        <input type="date" wire:model.live="dateFrom"
                            class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-[11px] font-bold outline-none focus:ring-4 focus:ring-blue-50 shadow-inner text-slate-600">
                    </div>

                    {{-- 3. Sampai --}}
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Sampai</label>
                        <input type="date" wire:model.live="dateTo"
                            class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-[11px] font-bold outline-none focus:ring-4 focus:ring-blue-50 shadow-inner text-slate-600">
                    </div>

                    {{-- 4. Judul Training --}}
                    <div class="space-y-2">
    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Judul Training</label>
    <select wire:model.live="trainingId"
        class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl text-[11px] font-bold outline-none focus:ring-4 focus:ring-blue-50 shadow-inner text-slate-600 appearance-none cursor-pointer">
        <option value="">-- SEMUA JUDUL --</option>
        @foreach($allTrainings as $t)
            {{-- Sekarang value-nya adalah judul teks ($t->title) --}}
            <option value="{{ $t->title }}">{{ $t->title }}</option>
        @endforeach
    </select>
</div>

                    {{-- 5. Tombol Reset --}}
                    <div>
                        <button wire:click="resetFilters"
                            class="w-full py-4 bg-blue-100 hover:bg-slate-200 text-black-400 font-black rounded-2xl text-[10px] uppercase tracking-widest transition-all active:scale-95 border border-slate-200/50 shadow-sm">
                            RESET FILTER
                        </button>
                    </div>
                </div>
            </div>

            {{-- TABLE SECTION --}}
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Department</th>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none text-center">Karyawan</th>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none text-center">Sudah Training</th>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none text-center">Belum Training</th>
                                <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest border-none">Penetration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($results as $row)
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-8 py-5 font-bold text-slate-700 uppercase text-xs tracking-tight">{{ $row->org_name }}</td>
                                <td class="px-8 py-5 text-center font-bold text-slate-400 text-xs">{{ number_format($row->total_emp) }}</td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <span class="font-black text-blue-600 text-xs">{{ number_format($row->trained) }}</span>
                                        <button wire:click="showDetail('trained', {{ $row->org_id }})"
                                            class="text-[9px] bg-blue-50 text-blue-600 border border-blue-100 px-3 py-1 rounded-lg font-black uppercase hover:bg-blue-600 hover:text-white transition-all">Lihat</button>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <span class="font-black text-red-500 text-xs">{{ number_format($row->total_emp - $row->trained) }}</span>
                                        <button wire:click="showDetail('untrained', {{ $row->org_id }})"
                                            class="text-[9px] bg-red-50 text-red-500 border border-red-100 px-3 py-1 rounded-lg font-black uppercase hover:bg-red-500 hover:text-white transition-all">Lihat</button>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4 w-48">
                                        <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-blue-600 transition-all duration-700" style="width: {{ $row->percentage }}%"></div>
                                        </div>
                                        <span class="font-black text-slate-700 text-[11px]">{{ $row->percentage }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-30">
                                        <h3 class="text-slate-800 font-black uppercase text-[11px] tracking-widest">Data Tidak Ditemukan</h3>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50/50">
                            <tr class="border-t-2 border-slate-100">
                                <td class="px-8 py-6 text-[10px] font-black text-slate-500 uppercase tracking-widest">Total Agregat</td>
                                <td class="px-8 py-6 text-center font-black text-slate-700 text-xs">{{ number_format($sumTotal) }}</td>
                                <td class="px-8 py-6 text-center font-black text-blue-600 text-xs">{{ number_format($sumTrained) }}</td>
                                <td class="px-8 py-6 text-center font-black text-red-500 text-xs">{{ number_format($sumTotal - $sumTrained) }}</td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-1 h-3 bg-white border border-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full bg-blue-600 shadow-sm" style="width: {{ $totalPct }}%"></div>
                                        </div>
                                        <span class="font-black text-blue-600 text-xs">{{ $totalPct }}%</span>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- MODAL DETAIL (Disesuaikan Style Contribution) --}}
            @if($selectedDept)
            <div class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-md">
                <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
                    <div class="p-8 border-b border-slate-50 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Daftar Karyawan</h3>
                            <p class="text-[10px] text-blue-500 font-black uppercase tracking-widest mt-1">
                                {{ $selectedType == 'trained' ? 'Sudah' : 'Belum' }} Training • {{ $selectedDept }}
                            </p>
                        </div>
                        <button wire:click="$set('selectedDept', null)" class="h-10 w-10 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all font-bold">✕</button>
                    </div>
                    <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar">
                        <div class="space-y-3">
                            @forelse($employeeList as $emp)
                            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-[1.5rem] border border-transparent hover:border-blue-100 transition-all">
                                <div class="h-10 w-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-xs shadow-lg shadow-blue-100">
                                    {{ strtoupper(substr($emp->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-[11px] font-black text-slate-700 uppercase tracking-tight">{{ $emp->name }}</p>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">NIK: {{ $emp->nik }}</p>
                                </div>
                            </div>
                            @empty
                            <p class="py-10 text-center text-slate-400 font-bold uppercase text-[10px] tracking-widest">Data tidak ditemukan.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="p-6 bg-slate-50/50">
                        <button wire:click="$set('selectedDept', null)" class="w-full py-4 bg-white border border-slate-200 text-slate-400 font-black rounded-2xl text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-all">Close</button>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>