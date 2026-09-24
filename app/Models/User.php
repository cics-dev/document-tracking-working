<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'role_id', 'position', 'office_id', 'signature', 'avatar'];

    /**
     * Get the user's avatar URL
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
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

        return asset('storage/'.$this->avatar);
    }

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

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * The system role assigned to this user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Determine whether the user has the administrator role.
     *
     * Authorization is based on the role record rather than the editable
     * position field or a hard-coded role ID.
     */
    public function isAdmin(): bool
    {
        if ($this->relationLoaded('role')) {
            return strtolower((string) $this->role?->role) === 'admin';
        }

        return $this->role()
            ->whereRaw('LOWER(role) = ?', ['admin'])
            ->exists();
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
