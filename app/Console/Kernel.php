<?php
namespace A    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Send birthday emails every day at 9:00 AM
        $schedule->command('email:send-birthday')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->onSuccess(function () {
                     \Log::info('✅ Birthday emails scheduled task completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('❌ Birthday emails scheduled task failed');
                 })
                 ->runInBackground();
    }le;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
    \App\Console\Commands\MakeAdminUser::class,
    \App\Console\Commands\ConvertPdfBackgroundToImage::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(\Illuminate\Console\Scheduling\Schedule $schedule): void
    {
        // Send birthday emails every day at 9:00 AM
        $schedule->command('email:send-birthday')
                 ->dailyAt('09:00')
                 ->withoutOverlapping()
                 ->onSuccess(function () {
                     \Log::info('✅ Birthday emails scheduled task completed successfully');
                 })
                 ->onFailure(function () {
                     \Log::error('❌ Birthday emails scheduled task failed');
                 })
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
