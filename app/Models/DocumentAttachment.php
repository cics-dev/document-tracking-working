<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAttachment extends Model
{
    protected $fillable = [
        'document_id',
        'attachment_document_id',
        'document_number',
        'status',
        'file_url',
    ];
}
