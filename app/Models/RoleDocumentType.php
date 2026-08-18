<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleDocumentType extends Model
{
    public $fillable = [
        'role_id',
        'document_type_id',
        'is_allowed',
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }
}
