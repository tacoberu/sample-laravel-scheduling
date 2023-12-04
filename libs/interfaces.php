<?php

namespace Taco\Scheduling;

use DateTime;



/**
 * Rozhraní jež poskytuje seznam reálně v systému (Laravel Scheduling, Crunz) dostupných Tasků.
 */
interface BankService
{
	function findAll(): array;
}


/**
 * Konkrétní implementace může zpracovat změnu tasku vlastním způsobem, anebo to jen delegovat na TasksDeposit
 */
interface ChangeOperation
{
	function persistChanges(int $id, array $changes);
}


interface CreateOperation
{
	function persistNew(TaskDef $x);
}


interface RemoveOperation
{
	function persistRemove(int $id);
}


interface RunnerService
{
	function doRunTasksAt(DateTime $at);
}


/**
 * Fasáda poskytující kompletní CRUD pro správu tasků.
 */
interface TasksDeposit
{
	/**
	 * @return array<TaskDef>
	 */
	function findAll(): array;


	function doPersistNewTask(TaskDef $nuevo);


	function doPersistChangeOfTask(int $id, TaskDef $nuevo);


	function doPersistRemoveOfTask(int $id);
}
