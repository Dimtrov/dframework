<?php

class HomeController extends dFramework\core\Controller
{
	public function index()
	{
		$this->view('/welcome')->render();
	}

	public function users($id, $name)
	{
		echo "ID : $id; Name: $name";

		//echo link_to('blog', 12, 'gg'); // localhost:3200/blog/joe/12/gg
	}

	protected function _filters() : array
	{
		return [
			SessionFilter::class
		];
	}
}
