<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use App\Models\Organization;

new class extends Component
{
    use WithPagination;

    public $activeTab = 'org'; 
    public $search = '';
    public $editingId = null;
    public $name = ''; 

    public function updatedActiveTab()
    {
        $this->reset(['search', 'editingId', 'name']);
        $this->resetPage();
    }

    public function edit($id)
    {
        $this->editingId = $id;
        
        if ($this->activeTab === 'org') {
            $this->name = Organization::find($id)->org_name;
        } else {
            // Sederhanakan query edit position
            $pos = DB::table('positions')->where('id', $id)->first();
            $this->name = $pos->position_name;
        }
    }

    public function save()
    {
        // Validasi disamakan: hanya butuh nama
        $this->validate([
            'name' => 'required|min:2'
        ]);

        if ($this->activeTab === 'org') {
            Organization::updateOrCreate(
                ['id' => $this->editingId], 
                ['org_name' => $this->name]
            );
        } else {
            // Simpan position tanpa mempedulikan organization_id
            DB::table('positions')->updateOrInsert(
                ['id' => $this->editingId],
                ['position_name' => $this->name]
            );
        }

        $this->resetForm();
        session()->flash('success', 'Data Master berhasil diperbarui.');
    }

    #[Computed]
    public function masterData()
    {
        $q = "%{$this->search}%";
        
        if ($this->activeTab === 'org') {
            return Organization::where('org_name', 'like', $q)
                ->orderBy('org_name')
                ->paginate(10);
        }
        
        // Query tabel position juga disederhanakan tanpa join
        return DB::table('positions')
            ->where('position_name', 'like', $q)
            ->orderBy('position_name')
            ->paginate(10);
    }

    public function resetForm() 
    { 
        $this->reset(['editingId', 'name']); 
    }

    public function delete($id) 
    { 
        if ($this->activeTab === 'org') {
            Organization::destroy($id);
        } else {
            DB::table('positions')->where('id', $id)->delete();
        }
        session()->flash('success', 'Data telah dihapus.');
    }
};