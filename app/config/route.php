<?php

/*
| -------------------------------------------------------------------
| ROUTE SETTINGS OF APPLICATION
| -------------------------------------------------------------------
| This file will contain the routing settings of your application.
|
| For complete instructions please consult the 'Route Configuration' in User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['default_controller']
|       This route indicates which controller class should be loaded if the
|       URI contains no data. In the above example, the "Home" class would be loaded.
|
*/


$route['default_controller'] = 'Home';

/*
$route['/works/([a-z-]+)/?$'] = 'Works::work';

$route['/posts']['GET'] = function () {
    echo 'dimitri ';
};

$route['/posts/([0-9]+)-([a-z\-0-9]+)'] = function ($a, $b) {
    echo "Article $a : $b";
};*/


/**
 * DON'T TOUCH THIS LINE. IT'S USING BY CONFIG CLASS
 */
return compact('route');