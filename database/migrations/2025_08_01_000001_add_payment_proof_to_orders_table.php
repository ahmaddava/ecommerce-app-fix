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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_proof')->nullable()->after('payment_reference');
            $table->text('admin_notes')->nullable()->after('payment_proof');
            $table->timestamp('payment_verified_at')->nullable()->after('admin_notes');
            $table->foreignId('verified_by')->nullable()->constrained('users')->after('payment_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_proof', 'admin_notes', 'payment_verified_at', 'verified_by']);
        });
    }
};

