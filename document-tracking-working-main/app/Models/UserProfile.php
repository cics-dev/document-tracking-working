<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = ['user_id', 'honorifics', 'given_name', 'middle_name', 'middle_initial', 'family_name', 'suffix', 'titles', 'gender'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}