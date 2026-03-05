<?php

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

new class extends Component
{
    #[Url]
    public $tab = 'activities';

    public $is_adding = false;
    public $selected_id = null;
    public $new_name = '';
    public $search = '';

    /**
     * Mengambil data dari tabel 'trainings' sesuai database hc-training
     */
    #[Computed]
    public function items()
    {
        $column = ($this->tab === 'activities') ? 'activity_name' : 'skill_name';

        return DB::table('trainings')
            ->selectRaw("MIN(id) as id, $column as name")
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->where($column, 'like', '%' . $this->search . '%')
            ->groupBy($column)->groupBy($column)
            ->orderBy($column, 'asc')
            ->get();
    }

    public function updatedTab()
    {
        $this->reset(['search', 'is_adding', 'selected_id', 'new_name']);
    }

    public function save()
    {
        $column = ($this->tab === 'activities') ? 'activity_name' : 'skill_name';

        $this->validate([
            'new_name' => [
                'required', 'min:2', 'string',
                Rule::unique('trainings', $column)->ignore($this->selected_id)
            ]
        ]);

        if ($this->selected_id) {
            // Update Data
            DB::table('trainings')->where('id', $this->selected_id)->update([
                $column => $this->new_name,
                'updated_at' => now()
            ]);
            $msg = "Atribut berhasil diperbarui.";
        } else {
            // Insert Data dengan mengisi kolom NOT NULL sesuai struktur DB
            DB::table('trainings')->insert([
                $column => $this->new_name,
                'title' => 'Master Data ' . $this->new_name, // Kolom 2: Title
                'held_by' => 'System Automation',            // Kolom 3: Held By
                'training_date' => now()->format('Y-m-d'),   // Kolom 6: Date
                'start_time' => '08:00:00',                  // Kolom 7: Time
                'finish_time' => '17:00:00',                 // Kolom 8: Time
                'fee' => 0,                                  // Kolom 9: Fee
                'created_at' => now(),                       // Kolom 10
                'updated_at' => now(),                       // Kolom 11
            ]);
            $msg = "Atribut baru berhasil ditambahkan.";
        }

        session()->flash('msg', $msg);
        $this->cancel();
    }

    public function edit($id, $name)
    {
        $this->selected_id = $id;
        $this->new_name = $name;
        $this->is_adding = false;
    }

    public function delete($id)
    {
        // Menghapus record secara permanen sesuai ID
        DB::table('trainings')->where('id', $id)->delete();
        session()->flash('msg', "Data berhasil dihapus dari database.");
    }

    public function cancel()
    {
        $this->reset(['new_name', 'selected_id', 'is_adding']);
    }
}; ?>