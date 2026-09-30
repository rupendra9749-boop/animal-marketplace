<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caretakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('headline')->nullable();
            $table->string('services')->nullable();
            $table->text('about')->nullable();
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->string('rate_unit', 10)->default('day');
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('state');
            $table->string('city');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('boarding')->default(false);
            $table->boolean('home_visit')->default(false);
            $table->boolean('available')->default(true);
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('caretaker_category', function (Blueprint $table) {
            $table->foreignId('caretaker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['caretaker_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caretaker_category');
        Schema::dropIfExists('caretakers');
    }
};
