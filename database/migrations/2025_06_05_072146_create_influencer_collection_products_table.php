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
        Schema::create("influencer_collection_products", function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger("influencer_id")->nullable();
            $table->foreign("influencer_id")->references("id")->on("influencers")->nullOnDelete();

            $table->unsignedBigInteger("collection_id")->nullable();
            $table->foreign("collection_id")->references("id")->on("collections")->nullOnDelete();

            $table->unsignedBigInteger("product_id")->nullable();
            $table->foreign("product_id")->references("id")->on("products")->nullOnDelete();

            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::dropIfExists("influencer_collection_products");
    }
};
