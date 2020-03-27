debugInfo.class.php
======

Credits
--------

This class is made by unreal4u (Camilo Sperberg). [http://unreal4u.com/](unreal4u.com).

About this class
--------

* When debugging, you need sometimes to check what's in a certain variable. This class does print that information
* It will have in special consideration the type of the variable you're printing: as such it will clearly indicate whether you're printing a boolean, empty string or even null
* Printing to screen is not practical? Print it to a file instead
* Tired of triggering errors? Convert them easily to exceptions with just one function call. From now on, every error will be converted to an exception instead
* Want to know how many time or memory a functionality in your script takes? Measure several of these benchmarks easily with this class!
* Format a unix timestamp? You can also do that easily with this class!

Basic usage
----------

<pre>include('debugInfo.class.php');
debug($variable);
</pre>

* Congratulations! You have just printed something to your screen!
* **Please see documentation folder for more options and advanced usage**

Version History
----------

* 0.1 :
    * Original class

* 0.2 :
    * Many improvements, such as:
        * General cleanup
        * Code is now more consistent
        * Extensively tested
* 0.3 :
    * Changed defaults to a much better representation
    * More checks for debugFirePHP
* 1.0 :
    * Integrated old class "benchmark" into this one
    * Some minor bug fixes
* 2.0 :
    * Compatible with composer
    * Refined auto-format output

Contact the author
-------

* Twitter: [@unreal4u](http://twitter.com/unreal4u)
* Website: [http://unreal4u.com/](http://unreal4u.com/)
* Github:  [http://www.github.com/unreal4u](http://www.github.com/unreal4u)
