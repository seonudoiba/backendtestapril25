<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'tenant_id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}