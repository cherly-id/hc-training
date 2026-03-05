<div class="min-h-screen bg-white p-4 lg:p-8">
    @if (session()->has('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
        class="fixed top-10 right-10 z-[9999] bg-emerald-500 text-white px-8 py-5 rounded-[2rem] shadow-2xl shadow-emerald-200 font-black text-[12px] uppercase tracking-[0.2em] animate-in fade-in slide-in-from-top-4 flex items-center gap-3">
        <span>✅</span>
        <span>{{ session('status') }}</span>
    </div>
    @endif
    <div class="max-w-7xl mx-auto space-y-8">

        {{-- HEADER SECTION --}}
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Employee Management</h2>
                    <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">Database Master Karyawan Jembo Cable</p>
                </div>

                <div class="flex items-center gap-3">
                    <button wire:click="$set('show_import_modal', true)"
                        class="px-6 py-3 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all text-[10px] uppercase tracking-widest">
                        IMPORT EXCEL
                    </button>

                    <button wire:click="exportExcel" wire:loading.attr="disabled"
                        class="inline-flex items-center px-6 py-3 bg-emerald-600 text-white font-black rounded-2xl hover:bg-emerald-700 shadow-lg shadow-emerald-100 transition-all text-[10px] uppercase tracking-widest gap-2">
                        <span wire:loading wire:target="exportExcel" class="animate-spin text-[12px]">🌀</span>
                        <span>EXPORT DATA</span>
                    </button>

                </div>
            </div>
        </div>

        {{-- FORM INPUT SECTION --}}
        <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-200">
            <h4 class="text-lg font-black text-slate-800 mb-6 uppercase tracking-tighter">{{ $editingId ? 'Update Employee' : 'Input Employee Baru' }}</h4>
            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">NIK</label>
                        <input type="text" wire:model="nik" class="w-full px-4 py-3 rounded-2xl border-none bg-slate-50 shadow-inner outline-none font-bold uppercase" placeholder="NIK...">
                        @error('nik') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Full Name</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-3 rounded-2xl border-none bg-slate-50 shadow-inner outline-none font-bold uppercase" placeholder="Nama Lengkap...">
                        @error('name') <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Organization</label>
                        <select wire:model="org_id" class="w-full px-4 py-3 rounded-2xl border-none bg-slate-50 shadow-inner outline-none font-bold uppercase cursor-pointer">
                            <option value="">-- PILIH --</option>
                            @foreach($this->orgs as $org)
                            <option value="{{ $org->id }}">{{ $org->org_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Position</label>
                        <select wire:model="position_id" class="w-full px-4 py-3 rounded-2xl border-none bg-slate-50 shadow-inner outline-none font-bold uppercase cursor-pointer">
                            <option value="">-- PILIH --</option>
                            @foreach($this->positions as $pos)
                            <option value="{{ $pos->id }}">{{ $pos->position_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Employee</label>
                        <select wire:model="status_employee" class="w-full px-4 py-3 rounded-2xl border-none bg-slate-50 shadow-inner outline-none font-bold uppercase cursor-pointer">
                            <option value="">-- PILIH --</option>
                            <option value="Permanent">Permanent</option>
                            <option value="Contract">Contract</option>
                            <option value="Probation">Probation</option>
                            <option value="Probation">Harian Lepas</option>
                            <option value="Probation">Management Trainee</option>

                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">System Status</label>
                        <select wire:model="status" class="w-full px-4 py-3 rounded-2xl border-none bg-slate-50 shadow-inner outline-none font-bold uppercase cursor-pointer">
                            <option value="Active">ACTIVE</option>
                            <option value="Inactive">INACTIVE</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="submit" class="px-10 py-3 bg-blue-600 text-white font-black rounded-2xl hover:bg-blue-700 shadow-lg shadow-blue-100 uppercase text-[11px] tracking-widest transition-all active:scale-95">
                        {{ $editingId ? 'Simpan Perubahan' : 'Simpan Employee Baru' }}
                    </button>
                    @if($editingId)
                    <button type="button" wire:click="resetForm" class="px-10 py-3 bg-slate-100 text-slate-400 font-black rounded-2xl hover:bg-slate-200 uppercase text-[11px] tracking-widest transition-all">Batal</button>
                    @endif
                </div>
            </form>
        </div>

        {{-- SEARCH & TABLE SECTION --}}
        <div class="space-y-4">
            <div class="bg-white p-4 rounded-[1.5rem] shadow-sm border border-slate-200 flex flex-col md:flex-row gap-3 items-center w-fit">
                <div class="relative w-full md:w-64">
                    <input type="text" wire:model.live.debounce.300ms="search" class="w-full pl-10 pr-4 py-2 bg-slate-50 border-none rounded-xl shadow-inner text-sm font-bold" placeholder="Cari NIK/Nama...">
                    <span class="absolute left-3 top-2.5 opacity-30">🔍</span>
                </div>
                <button wire:click="$set('search', '')" class="px-6 py-2 bg-slate-100 text-slate-400 font-black rounded-xl text-[10px] uppercase tracking-widest hover:bg-slate-200">Reset</button>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-blue-600 text-white">
                            <tr class="uppercase font-black text-[10px] tracking-widest">
                                <th class="px-6 py-5">NIK</th>
                                <th class="px-6 py-5">Name</th>
                                <th class="px-6 py-5">Organization</th>
                                <th class="px-6 py-5">Position</th>
                                <th class="px-6 py-5 text-center">Type</th>
                                <th class="px-6 py-5 text-center">Status</th>
                                <th class="px-6 py-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($this->employees as $e)
                            <tr class="hover:bg-slate-50/50 transition-all">
                                <td class="px-6 py-4 font-black text-slate-700 tracking-tighter">{{ $e->nik }}</td>
                                <td class="px-6 py-4 font-bold text-slate-600 uppercase">{{ $e->name }}</td>
                                <td class="px-6 py-4 text-slate-500 text-[11px] font-bold">{{ $e->organization->org_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-500 text-[11px] font-bold">{{ $e->position->position_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-[10px] font-black text-slate-400 uppercase">{{ $e->status_employee }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $e->status == 'Active' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                                        {{ $e->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="edit({{ $e->id }})" class="p-2 bg-amber-50 text-amber-500 rounded-xl hover:bg-amber-500 hover:text-white transition-all shadow-sm">✏️</button>
                                        <button wire:click="delete({{ $e->id }})" wire:confirm="Hapus data ini?" class="p-2 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition-all shadow-sm">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center text-slate-300 font-black uppercase tracking-widest opacity-30">Belum Ada Data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="flex flex-col md:flex-row justify-between items-center px-4">
            <div class="text-[10px] font-black text-black-300 uppercase tracking-widest">
                Showing {{ $this->employees->firstItem() }} - {{ $this->employees->lastItem() }} of {{ $this->employees->total() }} Employees
            </div>
            <div class="flex items-center gap-2 mt-4 md:mt-0">
                {{ $this->employees->links('livewire::simple-tailwind') }}
            </div>
        </div>
    </div>

    {{-- MODAL IMPORT EXCEL --}}

    @if($this->show_import_modal)
    <div class="fixed inset-0 z-[110] flex items-center justify-center p-6 bg-slate-900/70 backdrop-blur-md animate-in fade-in duration-300">
        <div class="bg-white w-full max-w-md rounded-[3rem] shadow-2xl border border-slate-200 overflow-hidden animate-in zoom-in-95 duration-300 flex flex-col">

            {{-- Header Modal --}}
            <div class="p-8 border-b border-slate-100 flex justify-between items-center bg-white text-slate-800">
                <div>
                    <h3 class="font-black uppercase text-sm tracking-tighter italic">Import Employee Data</h3>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">
                        Format: .xlsx / .xls |
                        <a href="{{ asset('Template Data Employee.xlsx') }}"
                            download
                            class="text-blue-600 hover:text-blue-800 underline decoration-2 underline-offset-4 transition-all italic">
                            DOWNLOAD TEMPLATE EXCEL
                        </a>
                    </p>
                </div>
                <button wire:click="$set('show_import_modal', false)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:text-rose-500 transition-all font-bold shadow-inner">✕</button>
            </div>

            @if (session()->has('error'))
            <div class="mx-8 mt-4 p-4 bg-rose-50 text-rose-500 rounded-2xl text-[10px] font-bold uppercase border border-rose-100">
                ⚠️ {{ session('error') }}
            </div>
            @endif

            <form wire:submit.prevent="importExcel" class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="block text-[10px] font-black text-slate-400 mb-2 tracking-widest uppercase">Pilih File Excel</label>
                    <input type="file" wire:model="excel_file"
                        class="w-full text-[11px] font-bold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer bg-slate-50 p-4 rounded-2xl shadow-inner border-none">
                    @error('excel_file') <span class="text-rose-500 text-[10px] font-bold uppercase">{{ $message }}</span> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-2xl font-black uppercase text-[11px] shadow-lg shadow-emerald-100 transition-all active:scale-95 flex justify-center items-center gap-2">
                    <span wire:loading wire:target="excel_file" class="animate-spin text-xs">🌀</span>
                    START IMPORT EMPLOYEES
                </button>
            </form>

            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-center">
                <button type="button" wire:click="$set('show_import_modal', false)" class="text-[9px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-all">Batal & Tutup</button>
            </div>
        </div>
    </div>
    @endif
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