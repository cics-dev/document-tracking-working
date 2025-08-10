<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleDocumentType extends Model
{
    public $fillable = [
        'role_id',
        'document_type_id',
        'is_allowed'
    ];
}
