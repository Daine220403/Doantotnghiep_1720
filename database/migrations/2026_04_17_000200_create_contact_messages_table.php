<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 180)->index();
            $table->string('phone', 20)->nullable();
            $table->string('subject', 160);
            $table->text('message');
            $table->enum('preferred_contact', ['phone', 'email', 'zalo'])->nullable();
            $table->enum('status', ['new', 'processing', 'resolved'])->default('new')->index();
            $table->timestamp('handled_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
