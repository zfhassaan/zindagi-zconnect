<?php

declare(strict_types=1);

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
        Schema::create('zindagi_zconnect_onboardings', function (Blueprint $table) {
            $table->id();
            $table->string('reference_id')->unique();
            $table->string('cnic');
            $table->string('full_name');
            $table->string('mobile_number');
            $table->string('email');
            $table->enum('status', [
                'initiated',
                'verified',
                'completed',
                'failed',
                'cancelled'
            ])->default('initiated');
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->json('verification_data')->nullable();
            $table->json('completion_data')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('cnic');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zindagi_zconnect_onboardings');
    }
};

