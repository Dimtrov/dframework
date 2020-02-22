<?php

/**
 * DON'T TOUCH THIS LINE. IT'S A REINITIALISATION OF THE PREVIOUS SETTING
 */
$config = null;


$config['default'] = [
    // fichiers de style des plugins
    'lib_styles' => [
        'bootstrap/css/bootstrap-4.3.1.min',
        'fonticons/fontawesome-4.7.0/css/font-awesome',
        'fonticons/fontawesome-5.9.0/css/all.min',
        'wow/animate-3.7.0'
    ],
    // fichiers de script des plugins
    'lib_scripts' => [
        'jquery/jquery-3.3.1.min', 'jquery/jquery-migrate.min',
        'bootstrap/js/popper.min', 'bootstrap/js/bootstrap-4.3.1.min',
        'jquery/jquery.easing.min',
        'wow/wow.min',
        'superfish/hoverIntent', 'superfish/superfish.min'
    ],
    // fichiers de style de l'application
    'styles' => [
        'default/template'
    ],
    // fichiers de script de l'application
    'scripts' => [
        'default/template'
    ],
];



/**
 * DON'T TOUCH THIS LINE. IT'S USING BY CONFIG CLASS
 */
return ['layout' => $config];