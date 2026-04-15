<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained('links')->cascadeOnDelete();
            $table->timestamp('clicked_at');

            $table->index(['link_id', 'clicked_at'], 'idx_link_clicked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
