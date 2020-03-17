<?php
use dFramework\components\rest\Controller As RestController;

class TestController extends RestController
{
    public function __construct() {
		parent::__construct();
		
	}

	
	public function index_get($id = null)
	{
		$data = $this->model->getArticles($id);
		
		$this->response($data, RestController::HTTP_OK);
	}
	
	
}