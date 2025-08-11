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
        // Schema::table('coupons', function (Blueprint $table) {
        //     $table->tinyInteger('coupon_type')->default(0)->comment("0 adi, 1 influencer");
        // });

        // Schema::table('coupons', function (Blueprint $table) {
        //     $table->dropColumn('type');
        // });

        // Schema::table('coupons', function (Blueprint $table) {
        //     $table->enum('type',['percentage','amount']);
        // });


        Schema::create('coupon_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('locale')->index();
            $table->string('title')->nullable();
            $table->unique(['coupon_id', 'locale']);
            $table->foreign('coupon_id')->references('id')->on('coupons')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
