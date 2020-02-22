<?php

use dFramework\core\Controller;

class WorksController extends Controller
{
    public function __construct()
    {
        parent::__construct();

     }

    /**
     * @return mixed|void
     * @throws ReflectionException
     * @throws \dFramework\core\exception\Exception
     */
    public function index()
    {
        $this->useObject(self::CACHE_OBJECT);

        if(! $content = $this->cache->read('works')) {
            $datas['works'] = $this->model->getWorks();
            $content = $this->view('index', $datas)->get();
            $this->cache->write('works', $content);
        }

    

 /*        $this->layout
            ->inject($content)
            ->setPageTitle('Nos travaux')
            ->launch();
  */   }

    public function work($workname)
    {
        $workname = strtolower($workname);

        if(!file_exists(VIEW_DIR.'works'.DS.'work_'.$workname.'.php')) {
            redirect('works');
        }
        $this->layout
            ->setPageTitle(ucwords($workname))
            ->add('work_'.$workname)
            ->launch();
    }
}