<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contribution_coverages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contribution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('coverage_year');
            $table->unsignedTinyInteger('coverage_month');
            $table->string('coverage_label', 50)->nullable();
            $table->timestamps();

            $table->index(['member_id', 'coverage_year', 'coverage_month'], 'idx_member_coverage_period');
            $table->unique(
                ['contribution_id', 'coverage_year', 'coverage_month'],
                'uniq_contribution_coverage_period'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contribution_coverages');
    }
};