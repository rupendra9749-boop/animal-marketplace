<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each seller approves (or declines) their own animals in an order. Orders that existed before this were
     * handled by the admin, so their items count as already approved.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('status', 10)->default('pending')->after('price');   // pending | approved | declined
            $table->timestamp('decided_at')->nullable()->after('status');
        });

        DB::table('order_items')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn(['status', 'decided_at']));
    }
};
