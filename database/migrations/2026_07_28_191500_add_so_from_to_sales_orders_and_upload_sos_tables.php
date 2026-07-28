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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('so_from')->nullable()->after('so_number');
            $table->string('billed_from')->nullable()->change();
            $table->string('billed_to')->nullable()->change();
        });

        Schema::table('upload_sos', function (Blueprint $table) {
            $table->string('so_from')->nullable()->after('so_number');
            $table->string('billed_from')->nullable()->change();
            $table->string('billed_to')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('so_from');
            $table->string('billed_from')->nullable(false)->change();
            $table->string('billed_to')->nullable(false)->change();
        });

        Schema::table('upload_sos', function (Blueprint $table) {
            $table->dropColumn('so_from');
            $table->string('billed_from')->nullable(false)->change();
            $table->string('billed_to')->nullable(false)->change();
        });
    }
};
