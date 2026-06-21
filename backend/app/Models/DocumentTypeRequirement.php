<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTypeRequirement extends Model
{
    protected $fillable = [
        'document_type_id',
        'required_document_type_id',
    ];

    public function documentType()
    {
        return $this->belongsTo(
            DocumentType::class,
            'document_type_id'
        );
    }

    public function requiredDocumentType()
    {
        return $this->belongsTo(
            DocumentType::class,
            'required_document_type_id'
        );
    }
}