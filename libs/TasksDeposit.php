<?php

namespace Taco\Scheduling;

use Illuminate\Support\Facades\File;


class TasksDepositPhpBased implements TasksDeposit
{

	function __construct(private string $sourcePath)
	{
	}



	/**
	 * @return array<TaskDef>
	 */
	function findAll(): array
	{
		// Prostor pro vlastní chytrystiku.
		return require($this->sourcePath);
	}



	function doPersistNewTask(TaskDef $nuevo)
	{
		$config = require $this->sourcePath;
		$config[] = $nuevo;
		File::put($configPath, implode("\n", [
			'<?php',
			'return ' . var_export($config, true) . ';',
		]));
	}



	function doPersistChangeOfTask(int $id, TaskDef $nuevo)
	{
		$config = require $this->sourcePath;
		$config[$id] = $nuevo;
		File::put($this->sourcePath, implode("\n", [
			'<?php',
			'return ' . var_export($config, true) . ';',
		]));
	}



	function doPersistRemoveOfTask(int $id)
	{
		$config = require $this->sourcePath;
		unset($config[$id]);
		$config = array_values($config);
		File::put($this->sourcePath, implode("\n", [
			'<?php',
			'return ' . var_export($config, true) . ';',
		]));
	}



/*	private static function export($src): string
	{
		switch (True) {
			case is_array($src):
				foreach ($src as $i => $v) {
					$src[$i] = self::export($v);
				}
				dump($src);
				return var_export($src, True);

			case is_object($src) && $src->getType() === TaskDef::Command:
				dump($src);
				return "TaskDef::Command('sample', '* * * * *')";

			case is_object($src) && $src->getType() === TaskDef::Exec:
				return "TaskDef::Exec('sample', '* * * * *')";

			default:
				dump($src);
				die("\n------\n" . __file__ . ':' . __line__ . "\n");
		}
	}*/
}



/**
 * Definice tasku nese informaci o tom, co se bude spouštět a kdy. Další možnosti rozšíření například o možnosti logování, mutextu, etc.
 */
class TaskDef
{
	const Command = 'Command';
	const Exec = 'Exec';

	private $type;
	private $command;
	private $cronExpression;
	private $description;


	/**
	 * Artisan příkaz. V případě Crunzu je třeba nalinkovat na správné umístění.
	 */
	static function Command(string $command, string $cronExpression, ?string $description)
	{
		return new self(self::Command, $command, $cronExpression, $description);
	}


	/**
	 * A nebo na brutála plnohodnotnou cestu.
	 */
	static function Exec(string $command, string $cronExpression, ?string $description)
	{
		return new self(self::Exec, $command, $cronExpression, $description);
	}


	private function __construct(string $type, string $command, string $cronExpression, ?string $description)
	{
		$this->type = $type;
		$this->command = $command;
		$this->cronExpression = $cronExpression;
		$this->description = $description;
	}


	function getType()
	{
		return $this->type;
	}


	/**
	 * Co se bude spouštět.
	 */
	function getCommand(): string
	{
		return $this->command;
	}


	/**
	 * Kde se bude spouštět ve formátu CRON "* * * * *"
	 */
	function getCronExpression(): string
	{
		return $this->cronExpression;
	}



	function getDescription(): ?string
	{
		return $this->description;
	}



	static function __set_state($data) {
		return new self($data['type'], $data['command'], $data['cronExpression'], $data['description']??null);
	}
}
