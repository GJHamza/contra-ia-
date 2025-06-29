<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->json('content');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['en_attente', 'en_cours', 'termine'])->default('en_attente');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->foreignId('container_id')->nullable()->constrained()->onDelete('set null');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
