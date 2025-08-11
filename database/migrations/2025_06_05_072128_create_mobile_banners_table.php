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
        Schema::create('mobile_banners', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->json('filter_conditions');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('mobile_banner_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mobile_banner_id');
            $table->string('locale')->index();
            $table->string('title');
            $table->longText('description');
            $table->unique(['mobile_banner_id', 'locale']);
            $table->foreign('mobile_banner_id')->references('id')->on('mobile_banners')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_banner_translations');
        Schema::dropIfExists('mobile_banners');
    }
};
