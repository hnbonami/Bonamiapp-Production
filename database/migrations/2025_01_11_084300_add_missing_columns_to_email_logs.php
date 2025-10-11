<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('email_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('email_logs', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('subject');
            }
            if (!Schema::hasColumn('email_logs', 'trigger_name')) {
                $table->string('trigger_name')->nullable()->after('template_id');
            }
            if (!Schema::hasColumn('email_logs', 'status')) {
                $table->enum('status', ['sent', 'failed', 'pending'])->default('pending')->after('trigger_name');
            }
            if (!Schema::hasColumn('email_logs', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('email_logs', 'error_message')) {
                $table->text('error_message')->nullable()->after('sent_at');
            }
            if (!Schema::hasColumn('email_logs', 'variables')) {
                $table->json('variables')->nullable()->after('error_message');
            }
        });
    }

    public function down()
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropColumn(['template_id', 'trigger_name', 'status', 'sent_at', 'error_message', 'variables']);
        });
    }
};