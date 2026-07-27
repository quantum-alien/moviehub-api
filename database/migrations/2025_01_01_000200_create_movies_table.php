<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->smallInteger('release_year');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('poster_path')->nullable();
            $table->string('director')->nullable();
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['release_year']);
            $table->index(['avg_rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
