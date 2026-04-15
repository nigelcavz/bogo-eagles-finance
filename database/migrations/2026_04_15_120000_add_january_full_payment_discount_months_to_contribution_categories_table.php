<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contribution_categories', function (Blueprint $table) {
            $table->unsignedTinyInteger('january_full_payment_discount_months')
                ->default(2)
                ->after('default_amount');
        });
    }

    public function down(): void
    {
        Schema::table('contribution_categories', function (Blueprint $table) {
            $table->dropColumn('january_full_payment_discount_months');
        });
    }
};
