
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
        Schema::table('distribution_product_details', function (Blueprint $table) {
            $table->unsignedInteger('quantity_stored')
                ->default(0)
                ->after('quantity_returned');

            $table->unsignedInteger('quantity_wasted')
                ->default(0)
                ->after('quantity_stored');

            $table->unsignedInteger('quantity_consumed')
                ->default(0)
                ->after('quantity_wasted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distribution_product_details', function (Blueprint $table) {
            $table->dropColumn([
                'quantity_stored',
                'quantity_wasted',
                'quantity_consumed',
            ]);
        });
    }
};
