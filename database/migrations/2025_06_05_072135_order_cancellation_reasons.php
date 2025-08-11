<?php

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
        Schema::create('order_cancellation_reasons', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_cancellation_reason_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cancel_id')->nullable();
            $table->foreign('cancel_id')->references('id')->on("order_cancellation_reasons")->nullOnDelete();
            $table->string('locale')->index();
            $table->string("title");
            $table->unique(['cancel_id', 'locale']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
