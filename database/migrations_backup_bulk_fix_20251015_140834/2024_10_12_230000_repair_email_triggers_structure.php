<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Voer de migratie uit om email_triggers tabel te repareren
     */
    public function up(): void
    {
        // Voeg ontbrekende kolommen toe aan email_triggers tabel
        if (Schema::hasTable('email_triggers')) {
            
            // Voeg description kolom toe als deze niet bestaat
            if (!Schema::hasColumn('email_triggers', 'description')) {
                Schema::table('email_triggers', function (Blueprint $table) {
                    $table->text('description')->nullable()->after('name');
                });
            }
            
            // Voeg trigger_type kolom toe
            if (!Schema::hasColumn('email_triggers', 'trigger_type')) {
                Schema::table('email_triggers', function (Blueprint $table) {
                    // Voeg toe na description, of na name als description niet bestaat
                    $afterColumn = Schema::hasColumn('email_triggers', 'description') ? 'description' : 'name';
                    $table->string('trigger_type')->default('scheduled')->after($afterColumn);
                });
            }
            
            // Voeg trigger_key kolom toe
            if (!Schema::hasColumn('email_triggers', 'trigger_key')) {
                Schema::table('email_triggers', function (Blueprint $table) {
                    $table->string('trigger_key')->nullable()->after('id');
                });
            }
            
            // Voeg trigger_data kolom toe
            if (!Schema::hasColumn('email_triggers', 'trigger_data')) {
                Schema::table('email_triggers', function (Blueprint $table) {
                    $table->json('trigger_data')->nullable()->after('trigger_type');
                });
            }
            
            // Voeg conditions kolom toe
            if (!Schema::hasColumn('email_triggers', 'conditions')) {
                Schema::table('email_triggers', function (Blueprint $table) {
                    $afterColumn = Schema::hasColumn('email_triggers', 'is_active') ? 'is_active' : 'trigger_data';
                    $table->json('conditions')->nullable()->after($afterColumn);
                });
            }
            
            // Voeg settings kolom toe
            if (!Schema::hasColumn('email_triggers', 'settings')) {
                Schema::table('email_triggers', function (Blueprint $table) {
                    $table->json('settings')->nullable()->after('conditions');
                });
            }
            
            // Voeg emails_sent kolom toe
            if (!Schema::hasColumn('email_triggers', 'emails_sent')) {
                Schema::table('email_triggers', function (Blueprint $table) {
                    $table->integer('emails_sent')->default(0)->after('settings');
                });
            }
            
            // Voeg last_run_at kolom toe
            if (!Schema::hasColumn('email_triggers', 'last_run_at')) {
                Schema::table('email_triggers', function (Blueprint $table) {
                    $table->timestamp('last_run_at')->nullable()->after('emails_sent');
                });
            }
            
            // Voeg created_by kolom toe
            if (!Schema::hasColumn('email_triggers', 'created_by')) {
                Schema::table('email_triggers', function (Blueprint $table) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('last_run_at');
                });
            }
            
            // Update bestaande triggers met basis trigger types
            $this->updateExistingTriggers();
            
            // Voeg unique constraint toe aan trigger_key (veilig)
            try {
                // Eerst alle NULL trigger_keys updaten met unieke waarden
                DB::table('email_triggers')
                    ->whereNull('trigger_key')
                    ->get()
                    ->each(function ($trigger, $index) {
                        DB::table('email_triggers')
                            ->where('id', $trigger->id)
                            ->update(['trigger_key' => 'trigger_' . $trigger->id . '_' . time() . '_' . $index]);
                    });
                
                Schema::table('email_triggers', function (Blueprint $table) {
                    $table->unique('trigger_key');
                });
            } catch (\Exception $e) {
                // Unique constraint mislukt, negeer error
                \Log::warning('Kon unique constraint niet toevoegen aan trigger_key: ' . $e->getMessage());
            }
        }
    }

    /**
     * Update bestaande triggers met basis data
     */
    private function updateExistingTriggers(): void
    {
        // Probeer bestaande triggers te herkennen op basis van naam
        $triggerMappings = [
            'Welkom Nieuwe Klanten' => 'welcome_customer',
            'Welkom Nieuwe Medewerkers' => 'welcome_employee', 
            'Testzadel Herinneringen' => 'testzadel_reminder',
            'Verjaardag Felicitaties' => 'birthday',
            'Klant Uitnodigingen' => 'klant_invitation',
            'Medewerker Uitnodigingen' => 'medewerker_invitation',
        ];
        
        foreach ($triggerMappings as $namePattern => $triggerType) {
            DB::table('email_triggers')
                ->where('name', 'LIKE', "%{$namePattern}%")
                ->whereNull('trigger_type')
                ->update([
                    'trigger_type' => $triggerType,
                    'trigger_key' => strtolower(str_replace(' ', '_', $namePattern)) . '_trigger',
                    'trigger_data' => json_encode([
                        'schedule' => 'daily',
                        'time' => '09:00'
                    ])
                ]);
        }
        
        // Update alle overige triggers zonder type
        DB::table('email_triggers')
            ->whereNull('trigger_type')
            ->update([
                'trigger_type' => 'unknown',
                'trigger_data' => json_encode([])
            ]);
    }

    /**
     * Draai de migratie terug
     */
    public function down(): void
    {
        if (Schema::hasTable('email_triggers')) {
            Schema::table('email_triggers', function (Blueprint $table) {
                $table->dropColumn([
                    'trigger_key',
                    'trigger_type', 
                    'trigger_data',
                    'conditions',
                    'settings',
                    'emails_sent',
                    'last_run_at',
                    'created_by'
                ]);
            });
        }
    }
};