<?php
class HomeController extends dFramework\core\Controller
{
	public function index()
	{
		$this->view('/welcome')->render();
	}
}
