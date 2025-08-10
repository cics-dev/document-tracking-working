<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $fillable = [
        'role',
        'description'
    ];

    public function role_document_types()
    {
        return $this->hasMany(RoleDocumentType::class, 'role_id', 'id');
    }
}
