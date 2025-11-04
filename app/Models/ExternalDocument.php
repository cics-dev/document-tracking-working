<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalDocument extends Model
{
    protected $fillable = [
        'from',
        'to_id',
        'subject',
        'received_date',
        'file_url',
        'file_type',
        'document_id',
    ];
}
