<?php
use dFramework\components\rest\Controller As RestController;

class TestController extends RestController
{
	

	
	public function index($id = null)
	{
		$this->allowed_methods('post');
		

		$data = $this->model->getArticles($id);
		
		echo $this->response($data);
	}
	
	
}