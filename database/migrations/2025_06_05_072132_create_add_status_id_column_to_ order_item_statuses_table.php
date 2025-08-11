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
        
        Schema::table('order_item_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('status_id')->nullable()->after('id');
            $table->foreign('status_id')->references('id')->on("statuses")->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
