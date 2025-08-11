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
        Schema::create('third_category_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('third_category_id');
            $table->string('locale')->index();
            $table->string('title');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keyword')->nullable();

            $table->unique(['third_category_id', 'locale']);
            $table->foreign('third_category_id')->references('id')->on('third_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('third_category_translations');
    }
};
