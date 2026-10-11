<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('origin');
            $table->string('title');
            $table->text('body');
            $table->string('image_url')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->string('status')->index();
            $table->unsignedBigInteger('job_id')->nullable()->index();
            $table->uuid('request_id')->nullable()->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
            $table->index('created_at');
        });

        Schema::create('push_notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('push_notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_user_id')->constrained()->cascadeOnDelete();
            $table->text('firebase_token');
            $table->string('status')->index();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['push_notification_id', 'device_user_id'], 'push_recipient_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notification_recipients');
        Schema::dropIfExists('push_notifications');
    }
};
