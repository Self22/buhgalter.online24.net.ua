<?php

namespace App\Console;

use App\Link;
use App\SiteSettings;
use App\TranslatedArticle;
use App\ForeignText;

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
            Link::telegram();
        })->hourly();

        $schedule->call(function () {
            Link::parse_ib();
        })->hourly();

        $schedule->call(function () {
            Link::parse_buhligazakon_news();
        })->hourly();

        $schedule->call(function () {
            Link::parse_buhligazakon_analytics();
        })->hourly();

        $schedule->call(function () {
            Link::parse_dtkt_news();
        })->hourly();

        $schedule->call(function () {
            Link::parse_911();
        })->hourly();

        $schedule->call(function () {
            Link::parse_balance();
        })->hourly();

        
        $schedule->call(function () {
            Link::parse_ifactor_news();
        })->hourly();

        $schedule->call(function () {
            Link::testParseResults();
        })->weekly();

        $schedule->call(function () {
            ForeignText::parseJournalOfAccountancy();
        })->daily();

        $schedule->call(function () {
            TranslatedArticle::makeTranslateArticle();
        })->twiceDaily(1, 13);
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
