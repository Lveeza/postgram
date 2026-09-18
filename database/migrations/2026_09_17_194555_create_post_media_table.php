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
        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'video', 'text']);
            $table->string('content');
            $table->unsignedInteger('order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['post_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.  
     */
    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
