<?php

use Kint\Kint;

class HomeController extends dFramework\core\Controller
{
	protected $layout = 'default';

	public function index()
	{
		$this->view('index', ['title' => 'sss'])->render();
	}
}
