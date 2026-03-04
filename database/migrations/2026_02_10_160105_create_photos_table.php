<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            // Siguraduhin na may 'albums' table ka muna bago ito i-run
            $table->foreignId('album_id')->constrained('albums')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path'); 
            $table->boolean('is_active')->default(true);
            $table->softDeletes(); // Para sa Recycle Bin feature mo
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('photos');
    }
};