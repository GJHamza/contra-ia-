<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('content'); // Contient le texte avec {{variables}}
            $table->string('language')->default('fr'); // fr / ar
            $table->string('category')->nullable(); // ex: Contrat travail, location...
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('templates');
    }
};
