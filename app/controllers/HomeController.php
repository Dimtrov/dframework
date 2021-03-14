<?php
class HomeController extends AppController
{
	public function index()
	{
		service('database')->query('select * from etudiants');
		$this->view('/welcome')->render();
	}

}
