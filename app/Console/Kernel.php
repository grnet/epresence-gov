<?php

namespace App\Console;


use App\Console\Commands\UpdateNamedUsersSettings;
use App\Jobs\Conferences\CloseConferences;
use App\Jobs\Conferences\EnableConferences;
use App\Jobs\Conferences\RemindUsersForClosingConferences;
use App\Jobs\Conferences\RemindUsersForUpcomingConferences;
use App\Jobs\Statistics\CalculateMonthlyDemoRoomStatistics;
use App\Jobs\Statistics\UpdateDailyStatistics;
use App\Jobs\Statistics\UpdateNowServiceUsageStatistics;
use App\Jobs\Statistics\UpdateServiceUsageAverageParticipants;
use App\Jobs\Statistics\UpdateTotalDeviceServiceUsageStatistics;
use App\Jobs\Users\AnonymizeConfirmedInactiveUsers;
use App\Jobs\Users\ClearIpAddresses;
use App\Jobs\Users\DeleteInactiveUnconfirmedUsers;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\DemoRoomController;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        UpdateNamedUsersSettings::class,
        ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // WARNING: scheduled tasks wont run if application is in maintenance mode
        // Every 15 minutes

        $schedule->call(function () {

            //Enable conferences
            EnableConferences::dispatch()->onQueue('high');

            //Close conferences
            CloseConferences::dispatch()->onQueue('high');

           //Remind users for upcoming conferences
           //Disabled during zoom migration 17/1/2019

            RemindUsersForUpcomingConferences::dispatch()->onQueue('high');

           //Users To Notify that conference is about to end
           //Disabled during zoom migration  17/1/2019

           RemindUsersForClosingConferences::dispatch()->onQueue('high');

        })->cron('0,15,30,45 * * * * *');


        //Every 5 minutes

        $schedule->call(function () {

            //Update daily statistics

           UpdateDailyStatistics::dispatch()->onQueue('low');

            //Update service usage now
           UpdateNowServiceUsageStatistics::dispatch()->onQueue('low');

        })->cron('*/5 * * * *');


        //Every 24h

        $schedule->call(function () {

            // Update service usage average participants
            UpdateServiceUsageAverageParticipants::dispatch()->onQueue('low');

        })->cron('0 0 * * * *');

        //Every day

        $schedule->call(function () {

            //Delete unconfirmed users who haven't joined a conference after 4 months

           DeleteInactiveUnconfirmedUsers::dispatch()->onQueue('low');

            //Update front page stats - service usage total stats

            UpdateTotalDeviceServiceUsageStatistics::dispatch()->onQueue('low');

            //Delete demo-room and recreate it

            DemoRoomController::recreate_demo_room();

        })->daily();

        //Every month

        $schedule->call(function () {

           // Calculate Monthly demo room statistics

           CalculateMonthlyDemoRoomStatistics::dispatch()->onQueue('low');

            //Process CDRS fo utilization/concurrency statistics

            //This should always run before  calculate_last_month_utilization_stats

            StatisticsController::calculate_last_month_concurrency_stats();

            StatisticsController::calculate_last_month_utilization_stats();

           // Clear ip addresses from conferences that happened 15 months ago

           ClearIpAddresses::dispatch()->onQueue('low');

           // Anonymize confirmed inactive users

           AnonymizeConfirmedInactiveUsers::dispatch()->onQueue('low');

        })->monthly();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
