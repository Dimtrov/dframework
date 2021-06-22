<?php

class AjaxOnly extends \Annotation {}

class Auth extends \Annotation {}

class Cors extends \Annotation {
    public $origin;
    public $methods;
}

class ForceHtts extends \Annotation {}

class IpBlackList extends \Annotation {}

class IpWhitelist extends \Annotation {}

class Methods extends \Annotation {}
