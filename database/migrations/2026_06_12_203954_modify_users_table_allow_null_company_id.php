<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop existing foreign key constraint first
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });
        
        // Modify company_id to allow null
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->change();
        });
        
        // Re-add foreign key constraint
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->foreignId('company_id')->nullable(false)->change();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }
};