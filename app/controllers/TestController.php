<?php
use dFramework\components\rest\Controller As RestController;

class TestController extends RestController
{
	

	
	public function index_get($id = null)
	{
		$data = $this->model->getArticles($id);
		
		echo $this->response($data);
	}
	
	
}