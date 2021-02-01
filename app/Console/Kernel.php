<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\StoreBusinessHour;
use App\Store;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('cache:clear')->cron('0 */4 * * *');;

        $schedule->call('App\Http\Controllers\StoreController@changeStoreStateBySchedule')->everyMinute();

        $schedule->call('App\Http\Controllers\OrderController@removeOldOrder')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
