<?php

class HomeController extends dFramework\core\Controller
{
	public function index()
	{

		$this->view('/welcome')->render();
	}

	protected function _filters() : array
	{
		return [
		//	SessionFilter::class
		];
	}
}
