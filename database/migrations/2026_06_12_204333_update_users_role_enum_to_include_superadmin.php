<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // MySQL doesn't support modifying enums directly, so we need to:
        // 1. Create a temporary column
        // 2. Copy data
        // 3. Drop old column
        // 4. Create new column with updated enum
        // 5. Copy data back
        
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_temp')->nullable();
        });
        
        DB::table('users')->update(['role_temp' => DB::raw('role')]);
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['SuperAdmin', 'Admin', 'Manager', 'Employee'])->default('Employee');
        });
        
        DB::table('users')->update(['role' => DB::raw('role_temp')]);
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_temp');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_temp')->nullable();
        });
        
        DB::table('users')->update(['role_temp' => DB::raw('role')]);
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['Admin', 'Manager', 'Employee'])->default('Employee');
        });
        
        DB::table('users')->update(['role' => DB::raw('role_temp')]);
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_temp');
        });
    }
};