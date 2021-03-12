<?php

use dFramework\core\loader\Service;

$event = Service::event();

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      $event->on('create', [$myInstance, 'myMethod']);
 */

$event->on('pre_system', function () use ($event) {
	/*
	 * --------------------------------------------------------------------
	 * Debug Toolbar Listeners. 
	 * --------------------------------------------------------------------
	 * If you delete, they will no longer be collected.
	 */
	if (!preg_match('#prod#i', config('general.environment')))
    {
        $event->on('db.query', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
    }

});
