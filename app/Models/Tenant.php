<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;
    
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = ['id', 'name', 'email', 'database', 'settings'];
    
    protected $casts = [
        'settings' => 'array',
    ];
    
    public function company()
    {
        return $this->hasOne(Company::class, 'tenant_id', 'id');
    }
}