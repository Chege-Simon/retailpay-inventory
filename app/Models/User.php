<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'branch_id',
        'store_id',
    ];

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
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBranchManager(): bool
    {
        return $this->role === 'branch_manager';
    }

    public function isStoreManager(): bool
    {
        return $this->role === 'store_manager';
    }

    public function canAccessStore(Store $store): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isBranchManager()) {
            return $this->branch_id === $store->branch_id;
        }

        if ($this->isStoreManager()) {
            return $this->store_id === $store->id;
        }

        return false;
    }
}
