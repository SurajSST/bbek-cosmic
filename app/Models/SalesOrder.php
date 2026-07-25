<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'so_number',
        'billed_via',
        'billed_to',
        'billed_status',
        'bill_no',
        'bill_image',
        'slip_image',
        'remarks',
        'description',
        'created_by',
    ];

    /**
     * Sales Order items relationship.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * User who created the sales order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessor for full bill image public URL.
     */
    public function getBillImageUrlAttribute(): ?string
    {
        return $this->bill_image ? Storage::disk('public')->url($this->bill_image) : null;
    }

    /**
     * Accessor for full slip image public URL.
     */
    public function getSlipImageUrlAttribute(): ?string
    {
        return $this->slip_image ? Storage::disk('public')->url($this->slip_image) : null;
    }

    /**
     * Scope to search by Sales Order (SO) or Bill No.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('so_number', 'like', "%{$search}%")
              ->orWhere('bill_no', 'like', "%{$search}%")
              ->orWhere('billed_to', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to sort by specific columns safely.
     */
    public function scopeSort(Builder $query, ?string $column = 'created_at', ?string $direction = 'desc'): Builder
    {
        $allowedColumns = ['so_number', 'billed_via', 'billed_to', 'billed_status', 'bill_no', 'created_at'];
        $column = in_array($column, $allowedColumns) ? $column : 'created_at';
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($column, $direction);
    }
}
