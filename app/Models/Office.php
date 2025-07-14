<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = ['name', 'abbreviation', 'office_type', 'head_id', 'office_logo'];

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function sentDocuments()
    {
        return $this->hasMany(Document::class, 'from_id');
    }

    public function receivedDocuments()
    {
        return $this->hasMany(Document::class, 'to_id');
    }
}