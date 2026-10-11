<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('external_user_id');
            $table->text('firebase_token');
            $table->string('platform');
            $table->string('name')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('invalidated_at')->nullable();
            $table->timestamp('last_registered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'external_user_id']);
            $table->index(['client_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_users');
    }
};
