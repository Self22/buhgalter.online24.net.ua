<?php

namespace App\Console;

use App\Link;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * php artisan make:command SendEmails.
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
        $schedule->call(function () {
            Link::parse_911();
        })->everyMinute();

        $schedule->call(function () {
            Link::parse_ib();
        })->everyMinute();

        $schedule->call(function () {
            Link::parse_buhligazakon();
        })->everyMinute();

        $schedule->call(function () {
            Link::parse_ifactor();
        })->everyMinute();

        $schedule->call(function () {
            Link::parse_dtkt();
        })->everyMinute();

        $schedule->call(function () {
            Link::parse_buhgalteria();
        })->everyMinute();




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
