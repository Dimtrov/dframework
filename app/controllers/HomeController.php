<?php

use dFramework\core\Controller;

class HomeController extends Controller
{

    public function index()
    {
        $this->loadLibrary('Image');

        $this->image->open('fond.png')
            ->write('dFramework', 50)
            ->filter(dF_Image::FILTER_INVERT)
            ->save('test.png')
            ->iShow();
    }

}