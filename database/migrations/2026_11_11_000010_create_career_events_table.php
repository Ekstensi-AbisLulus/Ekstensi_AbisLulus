<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users');
            $table->string('title', 200);
            $table->text('description');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('meeting_url', 2048);
            $table->enum('platform', ['zoom', 'google_meet', 'other']);
            $table->unsignedInteger('capacity')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->enum('category', ['hardskill', 'softskill'])->nullable();
            $table->string('thumbnail_url', 2048)->nullable();
            $table->timestamps();
            $table->index(['status', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_events');
    }
};
