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
        Schema::table("statuses", function(Blueprint $table){
            $table->unsignedBigInteger("related_id")->nullable();
            $table->foreign("related_id")->references("id")->on("statuses")->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("statuses", function(Blueprint $table){
            $table->dropColumn("related_id")->nullable();
        });
    }
};
