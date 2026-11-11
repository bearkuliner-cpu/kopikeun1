<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistributionProductDetail extends Model
{
    protected $fillable = [
        'distribution_id',
        'base_drink_id',
        'quantity_distributed',
        'quantity_returned',
        'quantity_stored',
        'quantity_wasted',
        'quantity_consumed',
    ];

    protected $casts = [
        'quantity_distributed' => 'integer',
        'quantity_returned' => 'integer',
        'quantity_stored' => 'integer',
        'quantity_wasted' => 'integer',
        'quantity_consumed' => 'integer',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(
            Distribution::class,
            'distribution_id'
        );
    }

    public function baseDrink(): BelongsTo
    {
        return $this->belongsTo(
            BaseDrink::class,
            'base_drink_id'
        );
    }
}
