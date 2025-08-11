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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("influencer_id")->nullable();
            $table->foreign("influencer_id")->references("id")->on("influencers")->nullOnDelete();
            $table->tinyInteger("status")->default(0);
            $table->timestamps();
        });

        Schema::create('collection_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collection_id')->nullable();
            $table->foreign('collection_id')->references('id')->on("collections")->nullOnDelete();
            $table->string('locale')->index();
            $table->string("title");
            $table->text("description");
            $table->unique(['collection_id', 'locale']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("collection_translations");
        Schema::dropIfExists("collections");
    }
};
