<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DocumentAttachment extends Model
{
    protected $fillable = [
        'document_id',
        'attachment_document_id',
<<<<<<< HEAD
        'name',
        'status',
        'file_url',
        'is_upload', 'file_type'
=======
        'document_number',
        'status',
        'file_url',
        'is_upload',
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    ];

    public function attachmentDocument()
    {
        return $this->belongsTo(Document::class, 'attachment_document_id');
    }

    // This accessor overrides file_url transparently
    protected function fileUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->is_upload) {
                return $this->attributes['file_url'];
            }

            return $this->attachmentDocument->file_url;
        });
    }
}
