<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'company_id', 'role'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'SuperAdmin';
    }

    public function isAdmin()
    {
        return $this->role === 'Admin';
    }

    public function isManager()
    {
        return $this->role === 'Manager';
    }

    public function isEmployee()
    {
        return $this->role === 'Employee';
    }
    
    // Override the default boot method to handle company_id for SuperAdmin
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            if ($user->role === 'SuperAdmin') {
                $user->company_id = null;
            }
        });
    }
}