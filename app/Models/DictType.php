<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DictType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'status',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(DictItem::class)->orderBy('sort')->orderBy('id');
    }
}
