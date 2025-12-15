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
        Schema::create('zindagi_zconnect_account_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('trace_no', 6)->index();
            $table->string('cnic', 13)->index();
            $table->string('mobile_no', 11);
            $table->string('merchant_type', 4);
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->string('response_code', 2)->nullable();
            $table->string('account_status', 1)->nullable();
            $table->string('account_title', 100)->nullable();
            $table->string('account_type', 2)->nullable();
            $table->string('is_pin_set', 1)->nullable();
            $table->boolean('success')->default(false);
            $table->timestamps();

            $table->index('created_at');
            $table->index(['cnic', 'mobile_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zindagi_zconnect_account_verifications');
    }
};

