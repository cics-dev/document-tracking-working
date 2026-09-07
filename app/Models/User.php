<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
<<<<<<< HEAD
use App\Notifications\ResetPasswordNotification;
=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
<<<<<<< HEAD
    protected $fillable = ['name', 'email', 'password', 'role_id', 'position', 'office_id', 'signature', 'avatar'];

    /**
     * Get the user's avatar URL
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }

        if (str_starts_with($this->avatar, 'assets/')) {
            return asset($this->avatar);
        }

        if (str_starts_with($this->avatar, 'storage/')) {
            return asset($this->avatar);
        }

        return asset('storage/' . $this->avatar);
    }
=======
    protected $fillable = ['name', 'email', 'password', 'position', 'office_id', 'signature'];
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function getIsHeadAttribute()
    {
        return ($this->office && $this->id === $this->office->head_id) ? 'Yes' : 'No';
    }
    
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }
<<<<<<< HEAD

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
=======
>>>>>>> d1c7b1feb3effde0c5d3ec144ba41064f14a3045
    
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    public function signatories()
    {
        return $this->hasMany(DocumentSignatory::class);
    }
}
