<?php
return array (
  0 =>
  \Taco\Scheduling\TaskDef::__set_state(array(
     'type' => 'Command',
     'command' => 'sample',
     'cronExpression' => '* 16 * * *',
     'description' => NULL,
  )),
  1 =>
  \Taco\Scheduling\TaskDef::__set_state(array(
     'type' => 'Exec',
     'command' => 'echo "Haf"',
     'cronExpression' => '* * * * *',
     'description' => NULL,
  )),
);
