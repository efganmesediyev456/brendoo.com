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
        Schema::create("balances", function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger("influencer_id")->nullable();
            $table->foreign("influencer_id")->references("id")->on("influencers")->nullOnDelete();
            $table->double("balance", 10,2)->nullable();
            $table->double("amount", 10,2)->nullable();
            $table->enum("type",['IN','OUT'])->default("IN");
            $table->string("balance_type")->nullable();
            $table->unsignedBigInteger("customer_id")->nullable();
            $table->foreign("customer_id")->references("id")->on("customers")->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::dropIfExists("balances");
    }
};
