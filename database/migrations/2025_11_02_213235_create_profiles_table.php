<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Profile;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('salutation')->nullable();           // Mr, Mrs, Dr, etc.
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();               // male, female, other, prefer-not-to-say
            $table->date('date_of_birth')->nullable();
            $table->string('telephone', 20)->nullable()->unique(); // E.164: +2547...
            $table->longText('biography')->nullable();
            $table->json('social_links')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country', 2)->nullable();           // ISO 3166-1 alpha-2 (KE, US, etc.)
            $table->string('zip_code')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('timezone')->default('Africa/Nairobi');
            $table->string('locale', 10)->default('en');
            $table->json('configuration')->nullable();
            $table->tinyInteger('status')->default(Profile::STATUS_PENDING);
            $table->softDeletes();
            $table->timestamps();

            // Optimized Indexes
            $table->index('user_id');
            $table->index('telephone');
            $table->index(['first_name', 'last_name']);
            $table->index('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
