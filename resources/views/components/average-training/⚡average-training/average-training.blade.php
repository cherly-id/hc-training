<div class="min-h-screen bg-white p-4 lg:p-8">
    <div class="max-w-7xl mx-auto space-y-8">

        {{-- HEADER SECTION: KOTAK JUDUL (Seragam dengan Dashboard) --}}
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Average Training Hours</h2>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">
                        Rata-rata jam pelatihan per karyawan
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    {{-- Year Selector: Dibuat lebih ramping & estetik --}}
                    <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-2xl border border-slate-100 shadow-inner">
                        <select wire:model.live="year" class="text-[10px] border-none focus:ring-0 rounded-xl bg-white font-black uppercase text-slate-600 py-2 px-4 shadow-sm cursor-pointer hover:bg-slate-50 transition-colors outline-none">
                            @for($y = date('Y'); $y >= 2026; $y--)
                                <option value="{{ $y }}">YEAR: {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Export Button --}}
                    <button wire:click="exportExcel" wire:loading.attr="disabled" 
                        class="flex items-center gap-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-4 px-8 rounded-2xl shadow-lg shadow-emerald-100 transition-all active:scale-95 text-[10px] tracking-widest uppercase">
                        <span wire:loading wire:target="exportExcel" class="animate-spin text-xs">🌀</span>
                        EXPORT EXCEL
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
                            <th class="px-8 py-5 text-[10px] font-black text-white uppercase tracking-widest sticky left-0 bg-blue-600 z-20 border-r border-blue-500">Department</th>
                            @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'] as $m)
                                <th class="px-3 py-5 text-[10px] font-black text-blue-100 uppercase tracking-widest text-center min-w-[70px] border-none">{{ $m }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @php
                            $colTotals = array_fill(1, 12, 0);
                            $orgCount = count($orgs ?? []) ?: 1;
                        @endphp

                        @forelse($orgs as $org)
                            @php $count = $emp_counts[$org->id] ?? 0; @endphp
                            <tr class="group hover:bg-slate-50 transition-colors">
                                <td class="px-8 py-5 sticky left-0 bg-white group-hover:bg-blue-50/30 border-r border-slate-50 z-10 shadow-sm transition-colors">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 text-xs uppercase tracking-tight">{{ $org->org_name }}</span>
                                        <span class="text-[9px] text-blue-400 font-black tracking-widest uppercase mt-1">{{ $count }} Employees</span>
                                    </div>
                                </td>
                                
                                @for ($m = 1; $m <= 12; $m++)
                                    @php
                                        $mins = $matrix[$org->id][$m] ?? 0; 
                                        $avg = ($count > 0) ? ($mins / 60) / $count : 0;
                                        $colTotals[$m] += $avg;
                                        $hasData = $avg > 0;
                                    @endphp
                                    <td class="px-3 py-5 text-center {{ $hasData ? 'bg-blue-50/20' : '' }}">
                                        @if($hasData)
                                            <span class="text-xs font-black text-blue-700">{{ number_format($avg, 2) }}</span>
                                        @else
                                            <span class="text-xs font-bold text-slate-200">-</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="px-8 py-24 text-center text-slate-300 font-black uppercase tracking-[0.2em] opacity-50">Data Tidak Ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50/50 border-t border-slate-100 font-black">
                        <tr>
                            <td class="px-8 py-5 sticky left-0 bg-slate-100/50 border-r border-slate-100 z-10 text-right text-[10px] uppercase tracking-widest text-blue-600">Overall Average</td>
                            @for ($m = 1; $m <= 12; $m++)
                                @php $overallAvg = $colTotals[$m] / $orgCount; @endphp
                                <td class="px-3 py-5 text-center text-xs {{ $overallAvg > 0 ? 'text-indigo-600' : 'text-slate-300' }}">
                                    {{ $overallAvg > 0 ? number_format($overallAvg, 2) : '-' }}
                                </td>
                            @endfor
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

            {{-- LEGEND --}}
            <div class="mt-6 flex items-start gap-3 p-4 bg-white border border-blue-100 rounded-2xl text-slate-600 text-xs shadow-sm">
                <span class="text-lg">💡</span>
                <div class="leading-relaxed">
                    <strong class="block mb-1 font-black text-blue-900 uppercase tracking-wide">Info Perhitungan</strong>
                    Average Training Hours = <code class="bg-blue-50 px-1.5 py-0.5 rounded text-blue-600 font-mono font-bold border border-blue-100">(Total jam training departemen / Jumlah karyawan departemen)</code>.
                </div>
            </div>

        </div>
    </div>