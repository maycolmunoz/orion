<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('disk')->default('public');
            $table->string('path')->unique();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('alt')->nullable();
            $table->foreignId('uploader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
