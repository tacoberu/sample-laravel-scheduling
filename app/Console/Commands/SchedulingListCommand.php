<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Taco\Scheduling\BankService;


/**
 * Obecná obálka pro zobrazení seznamu (kliedně můžeme použít originální implementaci). Slouží
 * ale jako demonstrace toho, že k seznamu těchto tasků se dostaneme programově.
 */
class SchedulingListCommand extends Command
{
	protected $signature = 'xscheduling:list';

	protected $description = 'Výpis existujících aktivních tasků.';

	private $bank;


	function __construct(BankService $bank)
	{
		parent::__construct();
		$this->bank = $bank;
	}



	function handle()
	{
        $header = ['id', 'expr', 'command', 'description'];
        $table = new Table($this->output);
        $table->setHeaders($header)
			->setRows(self::formatTaskList($this->bank->findAll()));
        $table->render();
	}



	private static function formatTaskList(array $src)
	{
		$ret = [];
		foreach ($src as $i => $x) {
			$ret[] = [$i, $x[0], $x[1], $x[2]];
		}
		return $ret;
	}
}
