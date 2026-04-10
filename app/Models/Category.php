<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    // ─── Relaciones ───────────────────────────────────────────

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
