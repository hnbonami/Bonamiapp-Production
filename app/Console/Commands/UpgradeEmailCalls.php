<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpgradeEmailCalls extends Command
{
    protected $signature = 'email:upgrade-calls';
    protected $description = 'Upgrade existing Mail::send calls to use new template system';

    public function handle()
    {
        $this->info('🔄 Upgrading email calls to use new template system...');
        
        // Show instructions for manual upgrade
        $this->info('');
        $this->info('📧 EMAIL CALL UPGRADE INSTRUCTIES:');
        $this->info('=====================================');
        
        $this->info('');
        $this->info('1️⃣ VERVANG IN JE CONTROLLERS:');
        $this->line('');
        $this->line('   VAN:');
        $this->line('   Mail::send(\'emails.birthday\', $data, function($message) use ($customer) {');
        $this->line('       $message->to($customer->email)->subject(\'Gefeliciteerd!\');');
        $this->line('   });');
        $this->line('');
        $this->line('   NAAR:');
        $this->line('   use App\\Helpers\\MailHelper;');
        $this->line('   MailHelper::sendBirthdayEmail($customer);');
        
        $this->info('');
        $this->info('2️⃣ TESTZADEL HERINNERINGEN:');
        $this->line('');
        $this->line('   VAN:');
        $this->line('   Mail::send(\'emails.testzadel-reminder\', compact(\'testzadel\', \'klant\'), function($message) use ($klant) {');
        $this->line('       $message->to($klant->email)->subject(\'Testzadel herinnering\');');
        $this->line('   });');
        $this->line('');
        $this->line('   NAAR:');
        $this->line('   use App\\Helpers\\MailHelper;');
        $this->line('   MailHelper::sendTestzadelReminder($testzadel, $klant);');
        
        $this->info('');
        $this->info('3️⃣ KLANT UITNODIGINGEN:');
        $this->line('');
        $this->line('   VAN:');
        $this->line('   Mail::send(\'emails.klant-invitation\', $data, function($message) use ($customer) {');
        $this->line('       $message->to($customer->email)->subject(\'Welkom!\');');
        $this->line('   });');
        $this->line('');
        $this->line('   NAAR:');
        $this->line('   use App\\Helpers\\MailHelper;');
        $this->line('   MailHelper::sendWelcomeCustomer($customer);');
        
        $this->info('');
        $this->info('4️⃣ MEDEWERKER UITNODIGINGEN:');
        $this->line('');
        $this->line('   VAN:');
        $this->line('   Mail::send(\'emails.medewerker-invitation\', $data, function($message) use ($employee) {');
        $this->line('       $message->to($employee->email)->subject(\'Welkom bij het team!\');');
        $this->line('   });');
        $this->line('');
        $this->line('   NAAR:');
        $this->line('   use App\\Helpers\\MailHelper;');
        $this->line('   MailHelper::sendWelcomeEmployee($employee);');
        
        $this->info('');
        $this->info('5️⃣ SMART FALLBACK (AUTOMATISCH):');
        $this->line('');
        $this->line('   Voor oude code die je nog niet wilt aanpassen:');
        $this->line('   VAN: Mail::send(...)');
        $this->line('   NAAR: MailHelper::smartSend(...)');
        $this->line('   (Gebruikt automatisch nieuwe templates als ze bestaan, anders oude systeem)');
        
        $this->info('');
        $this->info('✅ VOORDELEN VAN UPGRADE:');
        $this->line('   • 🎨 Professionele templates met je logo');
        $this->line('   • 📊 Email tracking en statistieken');
        $this->line('   • 🔧 Makkelijk aanpasbaar via web interface');
        $this->line('   • 📝 Email logs voor debugging');
        $this->line('   • 🤖 Automatische triggers mogelijk');
        
        $this->info('');
        $this->info('🎯 CHECK JE TEMPLATES:');
        $this->line('   Ga naar /admin/email/templates om te zien welke templates beschikbaar zijn');
        $this->line('   Ontbrekende templates? Klik "Migreer Templates" op /admin/email');
        
        return Command::SUCCESS;
    }
}