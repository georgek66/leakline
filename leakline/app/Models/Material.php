<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'workorder_id',
        'inventory_item_id',
        'item_name',
        'quantity',
        'unit',
        'cost'
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'workorder_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
