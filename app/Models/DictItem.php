<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DictItem extends Model
{
    protected $fillable = [
        'dict_type_id',
        'label',
        'value',
        'color',
        'sort',
        'status',
        'remark',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'status' => 'integer',
        ];
    }

    public function dictType(): BelongsTo
    {
        return $this->belongsTo(DictType::class);
    }
}
