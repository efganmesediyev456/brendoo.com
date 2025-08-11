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
        Schema::create('return_reasons', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger("status")->default(0);
            $table->timestamps();
        });

        Schema::create('return_reason_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_reason_id')->nullable();
            $table->foreign('return_reason_id')->references('id')->on("return_reasons")->nullOnDelete();
            $table->string('locale')->index();
            $table->string("title");
            $table->unique(['return_reason_id', 'locale']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("return_reason_translations");
        Schema::dropIfExists("return_reasons");
    }
};
