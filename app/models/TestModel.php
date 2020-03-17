<?php 
class TestModel extends dFramework\core\Model
{
	public function __construct() {
		parent::__construct();
	}
	
	
	public function getArticles($id = null) 
	{
		if(!empty($id)) 
		{
			return [
				'id' => $id,
				'name' => 'toto' 
			];
		}
		return [
			[
				'id' => 1,
				'name' => 'toto'
			],
			[
				'id' => 2,
				'name' => 'tata'
			],
		];
	}
}