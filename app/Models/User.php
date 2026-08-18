<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'role_id', 'position', 'office_id', 'signature'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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

    public function office()
    {
        return $this->belongsTo(Office::class)->withTrashed();
    }

    public function actingHeadOf()
    {
        return $this->hasMany(Office::class, 'acting_head_id');
    }

    /**
     * The OIC temporarily uses the designated head's role for that office's
     * workflow permissions. The user's stored role is never changed.
     */
    public function effectiveRoleId(): ?int
    {
        return $this->actingOffice()?->head?->role_id ?? $this->role_id;
    }

    public function isActingHead(): bool
    {
        return $this->actingOffice() !== null;
    }

    public function workflowOfficeIds(): array
    {
        return collect([$this->office_id])
            ->merge($this->actingHeadOf()->pluck('id'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function hasAccess(string $key): bool
    {
        $roleId = $this->effectiveRoleId();

        return $roleId !== null
            && Role::query()
                ->whereKey($roleId)
                ->whereHas('permissions', fn ($query) => $query->where('key', $key))
                ->exists();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    private function actingOffice(): ?Office
    {
        return $this->actingHeadOf()->with('head')->first();
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    public function steps()
    {
        return $this->hasMany(DocumentStep::class);
    }
}
