<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UploadSo extends Model
{
    use HasFactory;

    protected $fillable = [
        'so_number',
        'so_from',
        'billed_from',
        'billed_to',
        'status',
        'amount',
        'so_image',
        'slip_image',
        'remarks',
        'description',
        'created_by',
    ];

    /**
     * User who uploaded/created the SO.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessor for full SO image public URL.
     */
    public function getSoImageUrlAttribute(): ?string
    {
        return $this->so_image ? Storage::disk('public')->url($this->so_image) : null;
    }

    /**
     * Accessor for full slip image public URL.
     */
    public function getSlipImageUrlAttribute(): ?string
    {
        return $this->slip_image ? Storage::disk('public')->url($this->slip_image) : null;
    }

    /**
     * Scope to search by SO Number, SO From, Billed From, or Billed To.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('so_number', 'like', "%{$search}%")
              ->orWhere('so_from', 'like', "%{$search}%")
              ->orWhere('billed_from', 'like', "%{$search}%")
              ->orWhere('billed_to', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to sort by specific columns safely.
     */
    public function scopeSort(Builder $query, ?string $column = 'created_at', ?string $direction = 'desc'): Builder
    {
        $allowedColumns = ['so_number', 'so_from', 'billed_from', 'billed_to', 'status', 'amount', 'created_at'];
        $column = in_array($column, $allowedColumns) ? $column : 'created_at';
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($column, $direction);
    }
}
