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
        Schema::table('statuses', function (Blueprint $table) {
            // $table->boolean("deleteable")->default(true);
            // $table->boolean("is_paid_status")->default(false);
            // $table->boolean("is_office_status")->default(false);
            $table->boolean("is_box_status")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
