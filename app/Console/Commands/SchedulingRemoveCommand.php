<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Taco\Scheduling\RemoveOperation;


/**
 * Slouží jako demonstrace toho, že k seznamu těchto tasků se dostaneme programově.
 * Příklad užití:
 * `php artisan xscheduling:remove 1`
 */
class SchedulingRemoveCommand extends Command
{
	protected $signature = 'xscheduling:remove {id}';

	protected $description = 'Odstranění tasku.';

	private $operation;

	function __construct(RemoveOperation $operation)
	{
		parent::__construct();
		$this->operation = $operation;
	}


	function handle()
	{
		$this->operation->persistRemove($this->argument('id'));
	}
}
