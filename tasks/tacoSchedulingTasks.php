<?php

declare(strict_types=1);
/**
 * Crunz umožnuje více scriptů, kde každý script umožnuje vícero tasků. My na to rezignujeme
 * a budeme předpokládat jen ten náš script, který si tasky bude získávat z našeho zdroje. Vyřešíme
 * tím komplikaci, že Crunz vyžaduje, aby tasky byly definovány ve spešl souboru.
 */

use Taco\Scheduling\CrunzScheduleBuilder;
use Taco\Scheduling\TasksDepositPhpBased;

return (new CrunzScheduleBuilder(new TasksDepositPhpBased(__dir__ . '/../config/scheduling.php')))
	->createScheduler();
