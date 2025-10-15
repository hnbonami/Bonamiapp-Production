<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Veilige migratie om email_triggers tabel te repareren zonder after clauses
     */
    public function up(): void
    {
        if (!Schema::hasTable('email_triggers')) {
            // Tabel bestaat niet, maak volledig nieuwe aan
            Schema::create('email_triggers', function (Blueprint $table) {
                $table->id();
                $table->string('trigger_key')->unique()->nullable();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('trigger_type')->default('scheduled');
                $table->json('trigger_data')->nullable();
                $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->json('conditions')->nullable();
                $table->json('settings')->nullable();
                $table->integer('emails_sent')->default(0);
                $table->timestamp('last_run_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
            return;
        }

        // Tabel bestaat, voeg alleen ontbrekende kolommen toe
        $this->addMissingColumns();
        
        // Update bestaande data
        $this->updateExistingTriggers();
    }

    /**
     * Voeg ontbrekende kolommen toe zonder after clauses
     */
    private function addMissingColumns(): void
    {
        Schema::table('email_triggers', function (Blueprint $table) {
            if (!Schema::hasColumn('email_triggers', 'trigger_key')) {
                $table->string('trigger_key')->nullable();
            }
            if (!Schema::hasColumn('email_triggers', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('email_triggers', 'trigger_type')) {
                $table->string('trigger_type')->default('scheduled');
            }
            if (!Schema::hasColumn('email_triggers', 'trigger_data')) {
                $table->json('trigger_data')->nullable();
            }
            if (!Schema::hasColumn('email_triggers', 'conditions')) {
                $table->json('conditions')->nullable();
            }
            if (!Schema::hasColumn('email_triggers', 'settings')) {
                $table->json('settings')->nullable();
            }
            if (!Schema::hasColumn('email_triggers', 'emails_sent')) {
                $table->integer('emails_sent')->default(0);
            }
            if (!Schema::hasColumn('email_triggers', 'last_run_at')) {
                $table->timestamp('last_run_at')->nullable();
            }
            if (!Schema::hasColumn('email_triggers', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
        });
    }

    /**
     * Update bestaande triggers met basis data
     */
    private function updateExistingTriggers(): void
    {
        // Zorg dat alle triggers een trigger_type hebben
        DB::table('email_triggers')
            ->whereNull('trigger_type')
            ->orWhere('trigger_type', '')
            ->update(['trigger_type' => 'unknown']);

        // Probeer bestaande triggers te herkennen op basis van naam
        $triggerMappings = [
            'Welkom Nieuwe Klanten' => [
                'type' => 'welcome_customer',
                'key' => 'welcome_customer_trigger',
                'data' => ['event' => 'customer_created', 'delay_minutes' => 0]
            ],
            'Welkom Nieuwe Medewerkers' => [
                'type' => 'welcome_employee',
                'key' => 'welcome_employee_trigger', 
                'data' => ['event' => 'employee_created', 'delay_minutes' => 0]
            ],
            'Testzadel Herinneringen' => [
                'type' => 'testzadel_reminder',
                'key' => 'testzadel_reminder_trigger',
                'data' => ['schedule' => 'daily', 'time' => '10:00', 'days_before_due' => 7]
            ],
            'Verjaardag Felicitaties' => [
                'type' => 'birthday',
                'key' => 'birthday_trigger',
                'data' => ['schedule' => 'daily', 'time' => '09:00']
            ],
        ];
        
        foreach ($triggerMappings as $namePattern => $config) {
            $triggers = DB::table('email_triggers')
                ->where('name', 'LIKE', "%{$namePattern}%")
                ->get();
                
            foreach ($triggers as $trigger) {
                $updateData = [
                    'trigger_type' => $config['type'],
                    'trigger_data' => json_encode($config['data'])
                ];
                
                // Alleen trigger_key updaten als deze NULL is
                if (empty($trigger->trigger_key ?? null)) {
                    $updateData['trigger_key'] = $config['key'] . '_' . $trigger->id;
                }
                
                DB::table('email_triggers')
                    ->where('id', $trigger->id)
                    ->update($updateData);
            }
        }
        
        // Geef alle triggers zonder trigger_key een unieke key
        $triggersWithoutKey = DB::table('email_triggers')
            ->whereNull('trigger_key')
            ->orWhere('trigger_key', '')
            ->get();
            
        foreach ($triggersWithoutKey as $trigger) {
            DB::table('email_triggers')
                ->where('id', $trigger->id)
                ->update([
                    'trigger_key' => 'trigger_' . $trigger->id . '_' . time()
                ]);
        }
    }

    /**
     * Draai de migratie terug
     */
    public function down(): void
    {
        // Verwijder alleen de kolommen die we hebben toegevoegd
        if (Schema::hasTable('email_triggers')) {
            Schema::table('email_triggers', function (Blueprint $table) {
                $columnsToRemove = [];
                
                if (Schema::hasColumn('email_triggers', 'trigger_key')) {
                    $columnsToRemove[] = 'trigger_key';
                }
                if (Schema::hasColumn('email_triggers', 'trigger_type')) {
                    $columnsToRemove[] = 'trigger_type';
                }
                if (Schema::hasColumn('email_triggers', 'trigger_data')) {
                    $columnsToRemove[] = 'trigger_data';
                }
                if (Schema::hasColumn('email_triggers', 'conditions')) {
                    $columnsToRemove[] = 'conditions';
                }
                if (Schema::hasColumn('email_triggers', 'settings')) {
                    $columnsToRemove[] = 'settings';
                }
                if (Schema::hasColumn('email_triggers', 'emails_sent')) {
                    $columnsToRemove[] = 'emails_sent';
                }
                if (Schema::hasColumn('email_triggers', 'last_run_at')) {
                    $columnsToRemove[] = 'last_run_at';
                }
                if (Schema::hasColumn('email_triggers', 'created_by')) {
                    $columnsToRemove[] = 'created_by';
                }
                
                if (!empty($columnsToRemove)) {
                    $table->dropColumn($columnsToRemove);
                }
            });
        }
    }
};