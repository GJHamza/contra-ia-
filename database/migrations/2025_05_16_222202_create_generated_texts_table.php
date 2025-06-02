<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('generated_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('prompt');              // le prompt utilisé
            $table->longText('response');        // la réponse générée
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_texts');
    }
};
