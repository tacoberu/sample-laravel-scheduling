<?php

namespace Taco\Scheduling;

use Illuminate\Console\Scheduling\SchedulingMutex;
use DateTimeInterface;
use Illuminate\Console\Scheduling\Event;


class ScheduleMutex implements SchedulingMutex
{

	function create(Event $event, DateTimeInterface $time)
	{
		//~ return $this->cache->store($this->store)->add(
			//~ $event->mutexName().$time->format('Hi'), true, 3600
		//~ );
	}



	function exists(Event $event, DateTimeInterface $time)
	{
		//~ return $this->cache->store($this->store)->has(
			//~ $event->mutexName().$time->format('Hi')
		//~ );
	}



	function useStore($store)
	{
		//~ $this->store = $store;
		return $this;
	}

}
