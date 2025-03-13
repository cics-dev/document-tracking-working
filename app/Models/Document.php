<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['document_number', 'office_id', 'document_type_id', 'subject', 'content', 'created_by', 'status', 'date_sent'];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signatories()
    {
        return $this->hasMany(DocumentSignatory::class);
    }

    public function logs()
    {
        return $this->hasMany(DocumentLog::class);
    }
}