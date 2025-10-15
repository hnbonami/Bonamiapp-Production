<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspanningstests', function (Blueprint $table) {
            // Trainingstatus score velden (0-10)
            if (!Schema::hasColumn('inspanningstests', 'slaapkwaliteit')) {
                $table->integer('slaapkwaliteit')->nullable()->comment('Score 0-10: 0=slecht, 10=perfect');
            }
            if (!Schema::hasColumn('inspanningstests', 'eetlust')) {
                $table->integer('eetlust')->nullable()->comment('Score 0-10: 0=slecht, 10=perfect');
            }
            if (!Schema::hasColumn('inspanningstests', 'gevoel_op_training')) {
                $table->integer('gevoel_op_training')->nullable()->comment('Score 0-10: 0=slecht, 10=perfect');
            }
            if (!Schema::hasColumn('inspanningstests', 'stressniveau')) {
                $table->integer('stressniveau')->nullable()->comment('Score 0-10: 0=veel stress, 10=geen stress');
            }
            if (!Schema::hasColumn('inspanningstests', 'gemiddelde_trainingstatus')) {
                $table->decimal('gemiddelde_trainingstatus', 3, 1)->nullable()->comment('Automatisch berekend gemiddelde van scores');
            }
            
            // Open vraag velden
            if (!Schema::hasColumn('inspanningstests', 'training_dag_voor_test')) {
                $table->text('training_dag_voor_test')->nullable()->comment('Training 1 dag voor de test');
            }
            if (!Schema::hasColumn('inspanningstests', 'training_2d_voor_test')) {
                $table->text('training_2d_voor_test')->nullable()->comment('Training 2 dagen voor de test');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inspanningstests', function (Blueprint $table) {
            $table->dropColumn([
                'slaapkwaliteit',
                'eetlust', 
                'gevoel_op_training',
                'stressniveau',
                'gemiddelde_trainingstatus',
                'training_dag_voor_test',
                'training_2d_voor_test'
            ]);
        });
    }
};