<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sample', function (Taco\MyService $service) {
	$this->comment("Sample: " . $service->say());

	$date = now()->format('Y-m-d H:i:s');
	$mark = rand(0, 999);

	$log = $service->say() . ": ($mark) $date";

	// Zapisování do souboru
	$path = storage_path('logs/sample.log');
	file_put_contents($path, $log . PHP_EOL, FILE_APPEND);

})->purpose('Display an Sample');
