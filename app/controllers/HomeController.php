<?php

use dFramework\core\http\Middleware;

class HomeController extends AppController
{
	public function index()
	{
		return $this->render('/welcome');
	}

}
