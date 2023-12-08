<?php

namespace Taco\Scheduling;

use Symfony\Component\Process\Process;
use DateTime;
use RuntimeException;


/**
 * Crunz vyžaduje, aby script byl definovaný v adresáři. Nemůžeme jej mít
 * například v databázi a dynamicky získávat. Crunz navíc pro script uvádí
 * jméno scriptu, které následně není nikde používáno, ani nejde dodatečně zjistit.
 * Pořadí scriptů se odvozuje od poslední změny scriptu, což nepomáhá jeho identifikaci.
 *
 * Vyřešíme to tak že budeme mít jeden "náš" script (tacoSchedulingTasks.php), ve kterém
 * budeme získávat tasky pro script z našeho zdroje.
 */
class CrunzBankService implements BankService
{

	function findAll(): array
	{
		$command = implode(' ', [
			'vendor/bin/crunz',
			'schedule:list',
			'--format=json',
		]);

		$process = Process::fromShellCommandline($command);
		//~ $process->setTimeout(2 * 60); // 2minuty, @TODO kolik?
		$process->run();

		if ($process->isSuccessful()) {
			$xs = [];
			foreach (json_decode($process->getOutput()) as $row) {
				$xs[] = [
					$row->expression,
					$row->command,
					$row->task,
				];
			}
			return $xs;
		}
		throw new \RuntimeException($process->getErrorOutput(), $process->getExitCode());
	}

}



/**
 * Mohli bychom používat taky process, jenže on je ve skutečnosti ten defaultní cli dost zabugovanej...
 */
class CrunzChangeOperation implements ChangeOperation
{
	function __construct(
		private TasksDeposit $deposit,
		)
	{
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
class CrunzCreateOperation implements CreateOperation
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



class CrunzRemoveOperation implements RemoveOperation
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



class CrunzRunnerService implements RunnerService
{

	function doRunTasksAt(DateTime $at)
	{
		$command = implode(' ', [
			'vendor/bin/crunz',
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



class CrunzScheduleBuilder
{
	function __construct(private TasksDeposit $tasksDeposit)
	{
	}



	function createScheduler(): \Crunz\Schedule
	{
		$scheduler = new \Crunz\Schedule();
		foreach ($this->tasksDeposit->findAll() as $row) {
			self::buildTask($scheduler, $row);
		}
		return $scheduler;
	}



	private static function buildTask($scheduler, $row)
	{
		$task = $scheduler->run($row->getCommand());
		$task
			->description($row->getDescription())
			//~ ->preventOverlapping()
			->cron($row->getCronExpression())
			//~ ->weekdays()
		;
	}
}



class CrunzConfigurator
{

	function configure($app)
	{
		$app->singleton(BankService::class, CrunzBankService::class);
		$app->singleton(ChangeOperation::class, CrunzChangeOperation::class);
		$app->singleton(CreateOperation::class, CrunzCreateOperation::class);
		$app->singleton(RemoveOperation::class, CrunzRemoveOperation::class);
		$app->singleton(RunnerService::class, CrunzRunnerService::class);
	}

}
