<?php

use dFramework\core\http\Request;

class SessionFilter 
{

    public function process(Request $request, $delegate)
    {
        $response = $delegate->process($request);

        $body = '" toto "';

        $response->body($body);
        $response->send();
        exit();

        return $response;
    }
}