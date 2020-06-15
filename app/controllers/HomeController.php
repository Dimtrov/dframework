<?php
class HomeController extends dFramework\core\Controller
{
	public function index()
	{
		$this->view('index')->render();
		return;
	}
	public function users($id, $name)
	{
		echo "Utilisateur numero $id s'appelle $name";
	}
}
