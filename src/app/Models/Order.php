<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'external_id',
        'affiliate_id',
        'status',
        'total_value',
    ];
    
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total_value' => 'decimal:2',
        ];
    }
    
    protected $appends = [
        'status_name',
    ];
    
    // Retorna o nome do status do pedido.
    protected function statusName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status->label(),
        );
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }
}