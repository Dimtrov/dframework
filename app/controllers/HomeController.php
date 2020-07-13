<?php
class HomeController extends dFramework\core\Controller
{
	public function index()
	{
		$this->view('index', null, [
			'cache_name' => 'homepage',
			'cache_time' => 2,
			'compress_output' => true
		])->render();
	}
}
