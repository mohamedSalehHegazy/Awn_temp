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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name', 191);
			$table->string('fileable_type', 191);
			$table->unsignedBigInteger('fileable_id');
			$table->string('path', 191)->nullable();
			$table->string('label', 191)->nullable();
			$table->string('notes', 191)->nullable();
			$table->tinyInteger('order')->default(1);
			$table->boolean('is_active')->default(1);
            $table->timestamps();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
