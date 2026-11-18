<?php

namespace App\Livewire\SuperAdmin;

use App\Models\DeviceCatalog;
use Database\Seeders\DeviceCatalogSeeder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app', ['title' => 'Master Katalog Smartphone'])]
class DeviceCatalogManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $brandFilter = '';

    // Modal state
    public bool $showModal = false;

    public ?string $editingId = null;

    // Form fields
    public string $brand = '';

    public string $model_name = '';

    public string $series = '';

    public string $chipset = '';

    public string $specs_summary = '';

    public string $default_colors_text = '';

    public string $default_variants_text = '';

    public bool $is_verified = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBrandFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editDevice(string $id): void
    {
        $device = DeviceCatalog::findOrFail($id);
        $this->editingId = $device->id;
        $this->brand = $device->brand;
        $this->model_name = $device->model_name;
        $this->series = $device->series ?? '';
        $this->chipset = $device->chipset ?? '';
        $this->specs_summary = $device->specs_summary ?? '';
        $this->default_colors_text = is_array($device->default_colors) ? implode(', ', $device->default_colors) : '';
        $this->default_variants_text = is_array($device->default_variants) ? implode(', ', $device->default_variants) : '';
        $this->is_verified = (bool) $device->is_verified;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'brand' => 'required|string|max:100',
            'model_name' => 'required|string|max:150',
            'series' => 'nullable|string|max:100',
            'chipset' => 'nullable|string|max:150',
            'specs_summary' => 'nullable|string|max:500',
        ]);

        $colors = ! empty($this->default_colors_text)
            ? array_map('trim', explode(',', $this->default_colors_text))
            : null;

        $variants = ! empty($this->default_variants_text)
            ? array_map('trim', explode(',', $this->default_variants_text))
            : null;

        if ($this->editingId) {
            $device = DeviceCatalog::findOrFail($this->editingId);
            $device->update([
                'brand' => $this->brand,
                'model_name' => $this->model_name,
                'series' => $this->series ?: null,
                'chipset' => $this->chipset ?: null,
                'specs_summary' => $this->specs_summary ?: null,
                'default_colors' => $colors,
                'default_variants' => $variants,
                'is_verified' => $this->is_verified,
            ]);

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Katalog Diperbarui',
                'message' => "Model '{$this->brand} {$this->model_name}' berhasil diperbarui.",
            ]);
        } else {
            DeviceCatalog::create([
                'brand' => $this->brand,
                'model_name' => $this->model_name,
                'series' => $this->series ?: null,
                'chipset' => $this->chipset ?: null,
                'specs_summary' => $this->specs_summary ?: null,
                'default_colors' => $colors,
                'default_variants' => $variants,
                'is_verified' => $this->is_verified,
                'store_id' => null, // Global Reference
                'created_by' => Auth::id(),
            ]);

            $this->dispatch('toast', [
                'type' => 'success',
                'title' => 'Model Ditambahkan',
                'message' => "Model '{$this->brand} {$this->model_name}' berhasil ditambahkan ke master katalog.",
            ]);
        }

        $this->resetForm();
        $this->showModal = false;
    }

    public function deleteDevice(string $id): void
    {
        $device = DeviceCatalog::findOrFail($id);
        $name = "{$device->brand} {$device->model_name}";
        $device->delete();

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Model Dihapus',
            'message' => "Model '{$name}' telah dihapus dari katalog.",
        ]);
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->brand = '';
        $this->model_name = '';
        $this->series = '';
        $this->chipset = '';
        $this->specs_summary = '';
        $this->default_colors_text = '';
        $this->default_variants_text = '';
        $this->is_verified = true;
        $this->resetValidation();
    }

    public function reseedDefaults(): void
    {
        $seeder = new DeviceCatalogSeeder;
        $seeder->run();

        $this->dispatch('toast', [
            'type' => 'success',
            'title' => 'Sinkronisasi Selesai',
            'message' => 'Model-model smartphone populer berhasil disinkronkan ke database.',
        ]);
    }

    public function render(): View
    {
        $query = DeviceCatalog::query();

        if (! empty($this->brandFilter)) {
            $query->where('brand', $this->brandFilter);
        }

        if (! empty($this->search)) {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('model_name', 'like', $term)
                    ->orWhere('brand', 'like', $term)
                    ->orWhere('series', 'like', $term)
                    ->orWhere('chipset', 'like', $term);
            });
        }

        $devices = $query->orderBy('brand')->orderBy('model_name')->paginate(15);
        $brands = DeviceCatalog::distinct()->orderBy('brand')->pluck('brand');

        return view('livewire.super-admin.device-catalog-manager', [
            'devices' => $devices,
            'brands' => $brands,
            'totalDevices' => DeviceCatalog::count(),
            'totalBrands' => $brands->count(),
        ]);
    }
}
