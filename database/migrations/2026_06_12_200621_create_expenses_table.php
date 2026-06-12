<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->string('category');
            $table->timestamps();
            
            // Performance indexes
            $table->index(['company_id', 'created_at']);
            $table->index(['company_id', 'category']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};