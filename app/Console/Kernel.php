<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Taco\Scheduling\BankService;
use Taco\Scheduling\TasksDeposit;
use Taco\Scheduling\ScheduleMutex;
use LogicException;


class Kernel extends ConsoleKernel
{

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
		foreach ($this->app->make(TasksDeposit::class)->findAll() as $cmddef) {
			switch ($cmddef->getType()) {
				case $cmddef::Command:
					$entity = $schedule->command($cmddef->getCommand());
					break;
				case $cmddef::Exec:
					$entity = $schedule->exec($cmddef->getCommand());
					break;
				default:
					throw new LogicException('oops');
			}
			if ($cmddef->getDescription()) {
				$entity->description($cmddef->getDescription());
			}
			$entity->appendOutputTo(storage_path('logs/xschedule.log'))
				->cron($cmddef->getCronExpression())
				->withoutOverlapping()
				;
		}
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
