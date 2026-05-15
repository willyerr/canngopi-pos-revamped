<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftOrderItems extends Model
{
    protected $guarded = [];

    public function order() { return $this->belongsTo(DraftOrder::class); }
    public function menu() { return $this->belongsTo(Menu::class, 'item_id'); }
}
