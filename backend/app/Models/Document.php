<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doc_number',
        'file_mode',
        'document_type_id',
        'outlet_id',
        'party_id',
        'uploaded_by',
        'status',
        'remarks',
        'deleted_by',
    ];

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function files()
    {
        return $this->hasMany(DocumentFile::class);
    }

    public function links()
    {
        return $this->hasMany(DocumentLink::class);
    }

    public function queries()
    {
        return $this->hasMany(Query::class);
    }

    public function linkedDocuments()
    {
        return $this->hasMany(DocumentLink::class)->with('linkedDocument');
    }

    public function deletedBy()
    {
        return $this->belongsTo(
            User::class,
            'deleted_by'
        );
    }
}