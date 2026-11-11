<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'store_id',
    'name',
    'description',
])]
class ServiceCategory extends Model
{
    use BelongsToTenant, HasFactory, HasUlids;

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'category_id');
    }
}
