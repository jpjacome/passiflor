<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('calendar_token', 64)->unique()->nullable()->after('remember_token');
        });
        
        // Generate tokens for existing users
        \App\Models\User::whereIn('role', ['admin', 'therapist'])->each(function ($user) {
            $user->calendar_token = \Illuminate\Support\Str::random(64);
            $user->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('calendar_token');
        });
    }
};
