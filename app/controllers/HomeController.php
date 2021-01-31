<?php
class HomeController extends AppController
{
	public function index()
	{
		return $this->render('/welcome');
	}

}
