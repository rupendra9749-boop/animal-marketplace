<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('clinic_name')->nullable();
            $table->string('qualification')->nullable();
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->string('services')->nullable();
            $table->text('about')->nullable();
            $table->string('phone');
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->string('timings')->nullable();
            $table->boolean('home_visit')->default(false);
            $table->boolean('emergency')->default(false);
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('category_vet', function (Blueprint $table) {
            $table->foreignId('vet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->primary(['vet_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_vet');
        Schema::dropIfExists('vets');
    }
};
