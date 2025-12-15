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
        Schema::create('zindagi_zconnect_account_linkings', function (Blueprint $table) {
            $table->id();
            $table->string('trace_no', 6)->index();
            $table->string('cnic', 13)->index();
            $table->string('mobile_no', 11);
            $table->string('merchant_type', 4)->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->string('response_code', 2)->nullable();
            $table->string('account_title', 100)->nullable();
            $table->string('account_type', 20)->nullable();
            $table->string('otp_pin', 10)->nullable();
            $table->boolean('success')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zindagi_zconnect_account_linkings');
    }
};

