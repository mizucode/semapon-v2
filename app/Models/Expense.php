<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = [
        'store_id',
        'category_id',
        'user_id',
        'expense_number',
        'expense_date',
        'amount',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Expense $expense) {
            if (empty($expense->expense_number)) {
                $prefix = 'EXP-'.date('Ym').'-';
                $latest = static::withoutGlobalScopes()
                    ->where('store_id', $expense->store_id)
                    ->where('expense_number', 'like', "{$prefix}%")
                    ->latest('id')
                    ->value('expense_number');

                $nextNumber = 1;
                if ($latest) {
                    $parts = explode('-', $latest);
                    $nextNumber = ((int) end($parts)) + 1;
                }

                $expense->expense_number = $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
