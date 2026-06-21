<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentFile extends Model
{
    protected $fillable = [
        'document_id',
        'file_name',
        'file_path',
        'sort_order',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}