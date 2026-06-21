<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentLink extends Model
{
    protected $fillable = [
        'document_id',
        'linked_document_id',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function linkedDocument()
    {
        return $this->belongsTo(Document::class, 'linked_document_id');
    }
}