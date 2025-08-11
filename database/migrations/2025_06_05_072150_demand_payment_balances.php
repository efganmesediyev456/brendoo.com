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
        Schema::create("demand_payment_balances", function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger("influencer_id")->nullable();
            $table->foreign("influencer_id")->references("id")->on("influencers")->nullOnDelete();
            $table->string("type");
            $table->double("amount", 10,2)->default(0);
            $table->string("status")->default(1);
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
