<?php

namespace Taco\Scheduling;

use Illuminate\Console\Scheduling\Schedule;
use Symfony\Component\Process\Process;
use DateTime;
use RuntimeException;


/**
 * V Laravelu si můžeme dovolit šáhnout do vnitřností.
 */
class LaravelBankService implements BankService
{

	private $schedule;

	function __construct(Schedule $schedule)
	{
		$this->schedule = $schedule;
	}



	/**
	 * Vytahujeme si data přímo z interní implementace. Získáme tím jistotu, že to co se načte se opravdu načte.
	 */
	function findAll(): array
	{
		$xs = [];
		foreach ($this->schedule->events() as $row) {
			$xs[] = [
				$row->expression,
				$row->command,
				$row->description,
			];
		}
		return $xs;
	}

}



/**
 * Editace v Laravelu je vlastně generická. Ale nemusí být.
 */
class LaravelChangeOperation implements ChangeOperation
{
	private $deposit;

	function __construct(TasksDeposit $deposit)
	{
		$this->deposit = $deposit;
	}



	function persistChanges(int $id, array $changes)
	{
		if (!isset($this->deposit->findAll()[$id])) {
			throw new RuntimeException("Undefined task with id {$id}.");
		}
		$curr = $this->deposit->findAll()[$id];
		$this->deposit->doPersistChangeOfTask($id, self::buildTaskDef(array_merge(self::toArray($curr), $changes)));
	}



	private static function toArray(TaskDef $src)
	{
		return [
			'type' => $src->getType(),
			'command' => $src->getCommand(),
			'expression' => $src->getCronExpression(),
		];
	}



	private static function buildTaskDef(array $def): TaskDef
	{
		$def = (object) $def;
		switch ($def->type) {
			case TaskDef::Exec:
				return TaskDef::Exec($def->command, $def->expression, $def->description??Null);
			case TaskDef::Command:
				return TaskDef::Command($def->command, $def->expression, $def->description??Null);
			default:
				throw new LogicException("Oops");
		}
	}
}



/**
 * Editace v Laravelu je vlastně generická. Ale nemusí být.
 */
class LaravelCreateOperation implements CreateOperation
{
	private $deposit;

	function __construct(TasksDeposit $deposit)
	{
		$this->deposit = $deposit;
	}



	function persistNew(TaskDef $x)
	{
		$this->deposit->doPersistNewTask($x);
	}



	private static function toArray(TaskDef $src)
	{
		return [
			'type' => $src->getType(),
			'command' => $src->getCommand(),
			'expression' => $src->getCronExpression(),
		];
	}

}



class LaravelRemoveOperation implements RemoveOperation
{
	private $deposit;

	function __construct(TasksDeposit $deposit)
	{
		$this->deposit = $deposit;
	}



	function persistRemove(int $id)
	{
		$this->deposit->doPersistRemoveOfTask($id);
	}

}



class LaravelRunnerService implements RunnerService
{

	function doRunTasksAt(DateTime $at)
	{
		$command = implode(' ', [
			PHP_BINARY,
			defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan',
			'schedule:run',
			//~ '--no-ansi',
		]);

		$process = Process::fromShellCommandline($command);
		//~ $process->setTimeout(2 * 60); // 2minuty, @TODO kolik?
		$process->run();

		return [$process->getExitCode()
			, $process->getOutput()
			, $process->getErrorOutput()
			];
	}

}



class LaravelConfigurator
{

	function configure($app)
	{
		$app->singleton(BankService::class, LaravelBankService::class);
		$app->singleton(ChangeOperation::class, LaravelChangeOperation::class);
		$app->singleton(CreateOperation::class, LaravelCreateOperation::class);
		$app->singleton(RemoveOperation::class, LaravelRemoveOperation::class);
		$app->singleton(RunnerService::class, LaravelRunnerService::class);
	}

}
