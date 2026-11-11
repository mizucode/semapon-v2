<?php

namespace App\Livewire\Service;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\TenantContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Katalog Jasa Servis', 'breadcrumbs' => [['label' => 'Katalog Servis']]])]
class ServiceCatalog extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedCategory = '';

    public bool $showServiceModal = false;

    public bool $showCategoryModal = false;

    public ?string $editingServiceId = null;

    // Service fields
    public string $category_id = '';

    public string $name = '';

    public string $description = '';

    public float $price = 0;

    public int $estimated_duration_minutes = 60;

    public int $warranty_days = 30;

    public bool $is_active = true;

    // Category field
    public string $new_category_name = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function openCreateServiceModal(TenantContext $tenantContext): void
    {
        $store = $tenantContext->getStore();
        $this->reset(['name', 'description', 'price', 'editingServiceId', 'category_id']);
        $this->estimated_duration_minutes = 60;
        $this->warranty_days = (int) ($store ? $store->getSetting('default_warranty_days', 30) : 30);
        $this->is_active = true;
        $this->resetValidation();
        $this->showServiceModal = true;
    }

    public function saveService(): void
    {
        $validated = $this->validate([
            'category_id' => ['nullable', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_duration_minutes' => ['required', 'integer', 'min:5'],
            'warranty_days' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        if ($this->editingServiceId) {
            $service = Service::findOrFail($this->editingServiceId);
            $service->update($validated);
            $message = 'Layanan servis berhasil diperbarui.';
        } else {
            Service::create($validated);
            $message = 'Layanan servis baru berhasil ditambahkan ke katalog.';
        }

        $this->showServiceModal = false;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Katalog Disimpan',
            'message' => $message,
        ]);
    }

    public function editService(string $id): void
    {
        $service = Service::findOrFail($id);
        $this->editingServiceId = $service->id;
        $this->category_id = $service->category_id ?? '';
        $this->name = $service->name;
        $this->description = $service->description ?? '';
        $this->price = (float) $service->price;
        $this->estimated_duration_minutes = $service->estimated_duration_minutes;
        $this->warranty_days = $service->warranty_days;
        $this->is_active = $service->is_active;

        $this->resetValidation();
        $this->showServiceModal = true;
    }

    public function toggleServiceStatus(string $id): void
    {
        $service = Service::findOrFail($id);
        $service->update(['is_active' => ! $service->is_active]);

        $this->dispatch('toast', [
            'type' => 'info',
            'title' => 'Status Layanan Diubah',
            'message' => "Layanan {$service->name} kini ".($service->is_active ? 'Aktif' : 'Nonaktif').'.',
        ]);
    }

    public function createCategory(): void
    {
        $this->validate([
            'new_category_name' => ['required', 'string', 'max:100'],
        ]);

        ServiceCategory::create([
            'name' => $this->new_category_name,
        ]);

        $this->reset('new_category_name');
        $this->showCategoryModal = false;

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Kategori Ditambahkan',
            'message' => 'Kategori layanan servis berhasil dibuat.',
        ]);
    }

    public function render()
    {
        $categories = ServiceCategory::latest()->get();

        $services = Service::with('category')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->selectedCategory, fn ($q) => $q->where('category_id', $this->selectedCategory))
            ->latest()
            ->paginate(12);

        return view('livewire.service.service-catalog', [
            'services' => $services,
            'categories' => $categories,
        ]);
    }
}
