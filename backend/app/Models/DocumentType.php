<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $fillable = [
        'name',
        'linking_required',
        'is_active',
    ];

    public function requirements()
    {
        return $this->hasMany(
            DocumentTypeRequirement::class,
            'document_type_id'
        );
    }

    public function requiredBy()
    {
        return $this->hasMany(
            DocumentTypeRequirement::class,
            'required_document_type_id'
        );
    }
}