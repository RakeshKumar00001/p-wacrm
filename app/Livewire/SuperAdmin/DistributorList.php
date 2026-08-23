<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Distributor;

class DistributorList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    // Form
    public bool   $showForm  = false;
    public ?int   $editingId = null;
    public array  $form = [
        'name' => '', 'email' => '', 'phone' => '',
        'company' => '', 'country' => '', 'commission_pct' => 0,
        'status' => 'active', 'notes' => '',
    ];
    public string $successMsg = '';

    public function updatingSearch() { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->editingId = null;
        $this->form = ['name'=>'','email'=>'','phone'=>'','company'=>'','country'=>'','commission_pct'=>0,'status'=>'active','notes'=>''];
        $this->showForm = true;
        $this->successMsg = '';
    }

    public function openEdit(int $id): void
    {
        $d = Distributor::findOrFail($id);
        $this->editingId = $id;
        $this->form = $d->only(['name','email','phone','company','country','commission_pct','status','notes']);
        $this->showForm = true;
        $this->successMsg = '';
    }

    public function save(): void
    {
        $this->validate([
            'form.name'           => 'required|string|max:255',
            'form.email'          => 'required|email|max:255',
            'form.commission_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($this->editingId) {
            Distributor::findOrFail($this->editingId)->update($this->form);
            $this->successMsg = 'Distributor updated.';
        } else {
            Distributor::create($this->form);
            $this->successMsg = 'Distributor created.';
        }

        $this->showForm = false;
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $d = Distributor::findOrFail($id);
        $d->update(['status' => $d->status === 'active' ? 'suspended' : 'active']);
    }

    public function delete(int $id): void
    {
        Distributor::findOrFail($id)->delete();
        $this->successMsg = 'Distributor deleted.';
    }

    public function render()
    {
        $distributors = Distributor::withCount('businesses')
            ->when($this->search, fn($q) => $q->where('name','like','%'.$this->search.'%')
                ->orWhere('email','like','%'.$this->search.'%'))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()->paginate(20);

        return view('livewire.super-admin.distributor-list', compact('distributors'))
            ->layout('layouts.super-admin');
    }
}
