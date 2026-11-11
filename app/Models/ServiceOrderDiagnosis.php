<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'store_id',
    'service_order_id',
    'technician_id',
    'diagnosis_notes',
    'testing_checklist',
    'action_taken',
])]
class ServiceOrderDiagnosis extends Model
{
    use BelongsToTenant, HasFactory, HasUlids;

    protected function casts(): array
    {
        return [
            'testing_checklist' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'service_order_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
