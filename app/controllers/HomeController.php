<?php
/**
 *
 */
class HomeController extends AppController
{
	public function index()
	{
	    $this->view('/welcome')->render();
	}
}
