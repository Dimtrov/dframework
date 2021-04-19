<?php
/**
 *
 */
class HomeController extends \dFramework\components\rest\Controller
{
    /**
     * @AjaxOnly
     */
	public function index()
	{
	    echo Str::toCamel("un essai");
		//$this->view('/welcome')->render();
	}

}
