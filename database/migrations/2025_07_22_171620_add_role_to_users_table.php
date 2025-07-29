<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['cliente', 'admin', 'superadmin'])->default('cliente')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->timestamp('email_verified_at')->nullable()->change();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone']);
            $table->dropSoftDeletes();
        });
    }
};