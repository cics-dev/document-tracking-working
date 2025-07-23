<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSignatory extends Model
{
    protected $fillable = ['document_id', 'user_id', 'signatory_label', 'comments', 'status', 'viewed_at', 'signed_at', 'rejected_at', 'sequence'];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}