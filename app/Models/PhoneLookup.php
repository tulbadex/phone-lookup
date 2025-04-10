<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PhoneLookup extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    protected $casts = [
        'raw_data' => 'array'
    ];

    public $incrementing = false;
    protected $keyType = 'string';
}
