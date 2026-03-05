<div class="min-h-screen bg-white p-4 lg:p-8">
    {{-- KOTAK JUDUL: KHUSUS JUDUL SAJA --}}
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 mb-8">
        <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Executive Summary</h2>
        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">
            Human Capital Performance Overview
        </p>
    </div>

    {{-- BARIS FILTER & PERIODE: SEJAJAR DI BAWAH KOTAK JUDUL --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8 px-2">
        {{-- Keterangan Periode di Kiri --}}
        <div>
            <p class="text-sm text-slate-500 font-black uppercase tracking-widest">
                Periode: {{ $filter_month === 'all' ? "Tahun $filter_year" : ($months[$filter_month] ?? '') . " $filter_year" }}
            </p>
        </div>

        {{-- Dropdown Filter di Kanan --}}
        <div class="flex flex-wrap gap-2 bg-slate-50 p-2 rounded-2xl border border-slate-100">
            <select wire:model.live="filter_org" class="text-[10px] border-none focus:ring-0 rounded-lg bg-transparent font-black uppercase text-slate-700 cursor-pointer">
                <option value="all">-- Semua Dept --</option>
                @foreach($orgs_master as $o)
                <option value="{{ $o->id }}">{{ $o->org_name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filter_month" class="text-[10px] border-none focus:ring-0 rounded-lg bg-transparent font-black uppercase text-slate-700 cursor-pointer">
                <option value="all">-- Semua Bulan --</option>
                @foreach($months as $num => $name)
                <option value="{{ $num }}">{{ $name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filter_year" class="text-[10px] border-none focus:ring-0 rounded-lg bg-transparent font-black uppercase text-slate-700 cursor-pointer">
                @for($y = date('Y'); $y >= 2026; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- KONTEN UTAMA: STATS --}}
    <div class="space-y-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Total Jam Training --}}
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Total Jam Training</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-4xl font-black text-indigo-600">{{ number_format($total_hours, 1) }}</span>
                    <span class="text-slate-400 font-bold text-sm uppercase">Hrs</span>
                </div>
            </div>

            {{-- Avg. Hours --}}
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Avg. Hours / Person</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-4xl font-black text-indigo-600">{{ number_format($avg_training_hours, 2) }}</span>
                    <span class="text-slate-400 font-bold text-sm uppercase">Hrs</span>
                </div>
            </div>

            {{-- Karyawan Aktif --}}
            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Karyawan Aktif</span>
                <div class="flex items-baseline gap-1">
                    <span class="text-4xl font-black text-emerald-500">{{ $total_employees }}</span>
                    <span class="text-slate-400 font-bold text-sm uppercase">Employees</span>
                </div>
            </div>
        </div>

        {{-- Bagian Chart dan Penetrasi tetap sama --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- AREA CHART --}}
            <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <h4 class="text-lg font-black text-slate-800 mb-6 uppercase tracking-tight">Trend Jam Pelatihan ({{ $filter_year }})</h4>
                <div wire:key="chart-{{ $filter_year }}-{{ $filter_org }}">
                    {!! $chart->container() !!}
                </div>
            </div>

            {{-- PENETRATION LIST --}}
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <h4 class="text-lg font-black text-slate-800 mb-6 uppercase tracking-tight">Penetrasi Training</h4>
                <div class="max-h-[380px] overflow-y-auto pr-3 space-y-5 custom-scrollbar">
                    @foreach($penetration_list as $pen)
                    @php $pct = ($pen->total_emp > 0) ? round(($pen->trained_emp / $pen->total_emp) * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-[10px] mb-2 font-black uppercase">
                            <span class="text-slate-600 truncate w-32">{{ $pen->org_name }}</span>
                            <span class="text-indigo-600">{{ $pct }}%</span>
                        </div>
                        <div class="w-full bg-slate-50 h-2 rounded-full overflow-hidden shadow-inner">
                            <div class="bg-indigo-500 h-full transition-all duration-1000" @style(["width: $pct%"])></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script src="{{ $chart->cdn() }}"></script>
    {{ $chart->script() }}
</div>