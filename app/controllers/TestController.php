<?php
use dFramework\components\rest\Controller As RestController;

class TestController extends RestController
{
	
	public function index($id = null)
	{

		$articles =  $this->model->getArticles($id);

		$this->response(['success' => true, 'message' => 'Liste des articles', 'results' => $articles]);
	}
	
	
}