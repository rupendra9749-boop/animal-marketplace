<?php

use App\Support\Locations;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * People now have a phone number and a home city (anywhere in India), and there are more roles than
     * buyer / seller / admin. Existing rows keep working: new columns are empty until someone fills them in.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->string('state')->nullable()->after('phone');
            $table->string('city')->nullable()->after('state');
            $table->string('country')->default('India')->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('country');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->boolean('is_breeder')->default(false)->after('is_seller');
            $table->boolean('is_doctor')->default(false)->after('is_breeder');
            $table->boolean('is_caretaker')->default(false)->after('is_doctor');
        });

        Schema::table('animals', function (Blueprint $table) {
            $table->string('state')->nullable()->after('location');
        });

        Schema::table('vets', function (Blueprint $table) {
            $table->string('state')->nullable()->after('address');
        });

        // Older listings only stored the city name; work out the state so they show up in state-aware searches.
        foreach (DB::table('animals')->whereNotNull('location')->whereNull('state')->get(['id', 'location']) as $row) {
            if ($hit = Locations::findByCity($row->location)) {
                DB::table('animals')->where('id', $row->id)->update(['state' => $hit['state']]);
            }
        }
        foreach (DB::table('vets')->whereNull('state')->get(['id', 'city']) as $row) {
            if ($hit = Locations::findByCity($row->city)) {
                DB::table('vets')->where('id', $row->id)->update(['state' => $hit['state']]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('vets', fn (Blueprint $table) => $table->dropColumn('state'));
        Schema::table('animals', fn (Blueprint $table) => $table->dropColumn('state'));
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropColumn(['phone', 'state', 'city', 'country', 'latitude', 'longitude', 'is_breeder', 'is_doctor', 'is_caretaker']);
        });
    }
};
