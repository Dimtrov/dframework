<?php 
class FileController extends dFramework\core\Controller 
{
    public function __construct() 
    {
        parent::__construct();
    }

    public function index()
    {
        $this->loadLibrary('File');


        $a = $this->file->details(CONTROLLER_DIR.'tests'.DS.'lib');

        echo '<pre>';
        var_dump($a);
    }
}