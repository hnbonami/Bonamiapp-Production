<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create table with raw SQL if Schema doesn't work
        DB::statement("
            CREATE TABLE IF NOT EXISTS `email_subscriptions` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `email` varchar(255) NOT NULL UNIQUE,
                `subscriber_type` enum('klant','medewerker') DEFAULT 'klant',
                `subscriber_id` bigint unsigned NULL,
                `status` enum('subscribed','unsubscribed') DEFAULT 'subscribed',
                `unsubscribe_token` varchar(255) NULL UNIQUE,
                `subscribed_at` timestamp NULL,
                `unsubscribed_at` timestamp NULL,
                `unsubscribe_reason` varchar(255) NULL,
                `created_at` timestamp NULL,
                `updated_at` timestamp NULL,
                INDEX `email_status_index` (`email`, `status`),
                INDEX `subscriber_type_id_index` (`subscriber_type`, `subscriber_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS `email_subscriptions`");
    }
};