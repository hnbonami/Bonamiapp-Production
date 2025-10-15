<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('medewerkers', function (Blueprint $table) {
            $table->id();
            
            // Basis gegevens
            $table->string('voornaam');
            $table->string('achternaam');
            $table->string('email')->unique();
            $table->string('telefoonnummer')->nullable();
            $table->string('telefoon')->nullable();
            $table->string('mobiel')->nullable();
            $table->string('website')->nullable();
            
            // Adres gegevens
            $table->string('straatnaam')->nullable();
            $table->string('huisnummer')->nullable();
            $table->string('adres')->nullable();
            $table->string('postcode')->nullable();
            $table->string('stad')->nullable();
            $table->string('provincie')->nullable();
            $table->string('land')->default('Nederland');
            
            // Persoonlijke info
            $table->date('geboortedatum')->nullable();
            $table->integer('leeftijd')->nullable();
            $table->enum('geslacht', ['Man', 'Vrouw', 'Anders'])->nullable();
            $table->string('bsn')->nullable();
            $table->string('nationaliteit')->nullable();
            
            // Werkgerelateerd
            $table->enum('functie', ['fysiotherapeut', 'trainer', 'bikefit_specialist', 'admin', 'manager', 'stagiair'])->default('trainer');
            $table->enum('status', ['actief', 'inactief', 'verlof', 'proeftijd'])->default('actief');
            $table->date('in_dienst_sinds')->nullable();
            $table->date('uit_dienst')->nullable();
            $table->enum('contract_type', ['vast', 'tijdelijk', 'zzp', 'stage', 'vrijwilliger'])->default('vast');
            $table->decimal('uurloon', 8, 2)->nullable();
            $table->integer('uren_per_week')->nullable();
            
            // Kwalificaties
            $table->json('certificaten')->nullable(); // Array van certificaten
            $table->json('specialisaties')->nullable(); // Array van specialisaties
            $table->text('opleidingen')->nullable();
            $table->text('werkervaring')->nullable();
            $table->json('talen')->nullable(); // Array van talen
            
            // Contact voorkeuren
            $table->enum('voorkeur_contact', ['email', 'telefoon', 'sms', 'whatsapp'])->default('email');
            $table->boolean('nieuwsbrief')->default(true);
            $table->boolean('werkgerelateerde_emails')->default(true);
            
            // Emergency contact
            $table->string('noodcontact_naam')->nullable();
            $table->string('noodcontact_telefoon')->nullable();
            $table->string('noodcontact_relatie')->nullable();
            
            // Bankgegevens
            $table->string('iban')->nullable();
            $table->string('bank_naam')->nullable();
            $table->string('btw_nummer')->nullable();
            $table->string('kvk_nummer')->nullable();
            
            // Planning en beschikbaarheid
            $table->json('beschikbaarheid')->nullable(); // Array met dagen/tijden
            $table->integer('max_klanten_per_dag')->nullable();
            $table->boolean('weekend_beschikbaar')->default(false);
            $table->boolean('avond_beschikbaar')->default(false);
            
            // Profiel
            $table->text('bio')->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('avatar_url')->nullable();
            $table->json('social_media')->nullable();
            $table->text('notities')->nullable();
            $table->text('intern_notities')->nullable();
            
            // Gebruiker koppeling
            $table->unsignedBigInteger('user_id')->nullable();
            
            // Admin velden
            $table->unsignedBigInteger('aangemaakt_door')->nullable();
            $table->timestamp('laatste_login')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['email', 'status']);
            $table->index(['functie', 'status']);
            $table->index(['in_dienst_sinds', 'uit_dienst']);
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('aangemaakt_door')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('medewerkers');
    }
};