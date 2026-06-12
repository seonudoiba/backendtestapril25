<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['Admin', 'Manager', 'Employee'])->default('Employee');
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['company_id', 'role']);
            $table->unique(['company_id', 'email']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};