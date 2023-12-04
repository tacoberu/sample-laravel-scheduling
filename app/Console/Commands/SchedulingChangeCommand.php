<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Taco\Scheduling\ChangeOperation;
use RuntimeException;


/**
 * Slouží jako demonstrace toho, že k seznamu těchto tasků se dostaneme programově.
 * Příklad užití:
 * `php artisan xscheduling:change 1 --desc "" --expr "5 * * * *"`
 */
class SchedulingChangeCommand extends Command
{
	protected $signature = 'xscheduling:change {id : Required index of edited task. See to <info>list</info> command.} {--expr= : Planing expression, "5 * * * *" for example.} {--command= : The command to run.} {--desc= : Description of the task}';

	protected $description = 'Úprava existujícího tasku.';


	private $operation;


	function __construct(ChangeOperation $operation)
	{
		parent::__construct();
		$this->operation = $operation;
	}



	function handle()
	{
		try {
			$this->operation->persistChanges($this->argument('id'), array_filter([
				'expression' => $this->option('expr'),
				'command' => $this->option('command'),
				'description' => $this->option('desc'),
			], function($x) {
				return $x !== Null;
			}));
		}
		catch (RuntimeException $e) {
			$this->getErrorOutput()->writeln($e->getMessage());
			return 1;
		}
	}



	private function getErrorOutput()
	{
		return $this->output->getOutput()->getErrorOutput();
	}

}
