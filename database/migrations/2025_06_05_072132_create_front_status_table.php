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


        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string("title_ru")->nullable();
            $table->string("title_en")->nullable();
            $table->tinyInteger("type")->default(0)->comment("0 admin, 1 front");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('statuses');
    }
};
