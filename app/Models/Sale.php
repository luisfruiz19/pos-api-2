<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Sale extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $table = 'sales';

    protected $fillable = [
        'user_id',
        'cash_register_id',
        'total',
        'ganancia_total',
        'metodo_pago',
        'created_at',
    ];

    protected $casts = [
        'total'          => 'decimal:2',
        'ganancia_total' => 'decimal:2',
        'created_at'     => 'datetime',
    ];

    protected $appends = [
        'created_at_humans',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeHoy(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeEsteMes(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopePorMetodo(Builder $query, string $metodo): Builder
    {
        return $query->where('metodo_pago', $metodo);
    }

    public function scopeDeUsuario(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // cast created_at a time human for example : hace 1 hora, hace 2 horas, hace 1 día, etc
    public function getCreatedAtHumansAttribute(): string
    {
        return !$this->created_at ? '' : $this->created_at->diffForHumans();
    }
}
