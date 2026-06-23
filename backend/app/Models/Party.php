<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}