<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Taco\Scheduling\CreateOperation;
use Taco\Scheduling\TaskDef;


/**
 * Slouží jako demonstrace toho, že k seznamu těchto tasků se dostaneme programově.
 * Příklad užití:
 * `php artisan xscheduling:create "5 * * * * *" "php -v"`
 */
class SchedulingCreateCommand extends Command
{
	protected $signature = 'xscheduling:create {type : exec | command} {expr : cron mask} {cmd : planned process} {--desc=}';

	protected $description = 'Přidání nového tasku.';

	private $operation;


	function __construct(CreateOperation $operation)
	{
		parent::__construct();
		$this->operation = $operation;
	}



	function handle()
	{
		switch ($this->argument('type')) {
			case 'exec':
				$task = TaskDef::Exec($this->argument('cmd'), $this->argument('expr'));
				break;
			case 'command':
				$task = TaskDef::Command1($this->argument('cmd'), $this->argument('expr'));
				break;
			default:
				throw new LogicException("Oops");
		}
		$this->operation->persist($task);
	}
}
