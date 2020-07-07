<?php

use dFramework\core\http\Request;

class HomeController extends dFramework\core\Controller
{
	public function index()
	{
		echo link_to('blog', 12, 'toto');
	}
	public function users($id, $name)
	{
		echo "Utilisateur numero $id s'appelle $name";
	}







	public function before(Request $request)
	{
		$request->uri = 'toto';
		return $request;
	}
}
