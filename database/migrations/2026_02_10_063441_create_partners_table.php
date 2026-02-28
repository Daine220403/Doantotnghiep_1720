<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);

            $table->enum('type', ['hotel', 'transport', 'restaurant', 'attraction', 'other'])
                  ->default('other')->index();

            $table->string('phone', 20)->nullable()->index();
            $table->string('email', 150)->nullable()->index();
            $table->string('address', 255)->nullable();

            $table->enum('status', ['active', 'inactive', 'locked'])
                  ->default('active')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
