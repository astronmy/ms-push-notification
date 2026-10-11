<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->nullable()->index();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action')->index();
            $table->string('method', 16)->index();
            $table->unsignedSmallInteger('status')->index();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
            $table->index(['client_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
