<?php
// ...existing code...
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('openai_usages', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('tokens');
            $table->decimal('cost', 8, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('openai_usages');
    }
};
