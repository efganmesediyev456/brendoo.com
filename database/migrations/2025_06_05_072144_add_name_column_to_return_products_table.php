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
        Schema::table('return_products', function (Blueprint $table) {
            // $table->string("name")->nullable();
            // $table->string("email")->nullable();
            // $table->string("address")->nullable();
            // $table->unsignedBigInteger("return_reason_id")->nullable();
            // $table->foreign("return_reason_id")->references("id")->on("return_reasons")->nullOnDelete();
            // $table->longText('notes')->nullable();
            $table->string('phone')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      
    }
};
