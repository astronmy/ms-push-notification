<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true)->index();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('notes')->nullable();
            $table->string('api_key_hash', 64)->unique();
            $table->string('api_key_prefix', 16);
            $table->string('api_key_last_four', 8);
            $table->string('firebase_project_id')->nullable()->index();
            $table->longText('firebase_service_account')->nullable();
            $table->unsignedInteger('rate_limit_per_minute')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
