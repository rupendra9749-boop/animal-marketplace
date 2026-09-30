<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * listing_type: "sale" (normal listing), "breeding" (offered for breeding service only,
     * not for sale) or "both". Every existing row keeps behaving exactly as before ("sale").
     */
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->string('listing_type', 10)->default('sale')->after('is_active');
            $table->decimal('breeding_fee', 10, 2)->nullable()->after('listing_type');
        });
    }

    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
            $table->dropColumn(['listing_type', 'breeding_fee']);
        });
    }
};
