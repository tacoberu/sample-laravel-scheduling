<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Taco\Scheduling\RunnerService;
use DateTime;


/**
 * Je třeba zaregistrovat do systémového cron/scheduleru. Ten bude spouštět tento command, a ten bude spouštět plánované Tasky.
 * Příklad užití:
 * `php artisan xscheduling:run`
 * Příklad nastavení v cronu:
 * `* * * * * cd /project && php artisan scheduling:run`
 */
class SchedulingRunCommand extends Command
{
	protected $signature = 'xscheduling:run';

	protected $description = 'Run the scheduled commands';

	private $runner;


	function __construct(RunnerService $runner)
	{
		parent::__construct();
		$this->runner = $runner;
	}



	function handle()
	{
		list($code, $out, $err) = $this->runner->doRunTasksAt(new DateTime());
		if ($out) {
			$this->getOutput()->writeln($out);
		}
		if ($err) {
			$this->getErrorOutput()->writeln($err);
		}
		return $code;
	}



	private function getErrorOutput()
	{
		return $this->output->getOutput()->getErrorOutput();
	}

}
