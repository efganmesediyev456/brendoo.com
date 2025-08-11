<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('influencer_id');
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->timestamps();
            $table->foreign('influencer_id')->references('id')->on('influencers')->onDelete('cascade');
        });

        Schema::create('story_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('story_id');
            $table->string('locale')->index();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unique(['story_id', 'locale']);
            $table->foreign('story_id')
                  ->references('id')
                  ->on('stories')
                  ->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_translations');
        Schema::dropIfExists('stories');
    }
};
