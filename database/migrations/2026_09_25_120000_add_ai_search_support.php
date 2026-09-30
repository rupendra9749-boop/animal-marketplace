<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Doctors can now come from an AI web search. Each one remembers where it came from (the web page it was
     * found on) and whether a person has checked it, and every AI search is logged so it can be rate limited.
     */
    public function up(): void
    {
        Schema::table('vets', function (Blueprint $table) {
            $table->string('source', 10)->default('manual')->after('is_active');   // manual | user | ai
            $table->string('source_url', 500)->nullable()->after('source');
            $table->boolean('is_verified')->default(true)->after('source_url');
        });

        DB::table('vets')->whereNotNull('user_id')->update(['source' => 'user']);

        Schema::create('ai_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 20)->default('vet');
            $table->string('state');
            $table->string('city');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('found_count')->default(0);
            $table->string('status', 20);      // ok | empty | error
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['kind', 'state', 'city', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_searches');
        Schema::table('vets', fn (Blueprint $table) => $table->dropColumn(['source', 'source_url', 'is_verified']));
    }
};
