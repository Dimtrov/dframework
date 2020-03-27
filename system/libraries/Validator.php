<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.0
 */

use dFramework\core\data\Request;
use Valitron\Validator;

/**
 * Validator
 *
 * The dFramework Form Validator
 *  Valid a form data based on vlucas/valitron dependancy
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Validator.html
 * @since       3.0
 * @file        /system/libraries/Validator.php
 */

class dF_Validator
{
    /**
     * @var Validator
     */
    private $validator = null;
    
    /**
     * Initialize validation process. Entry point of all 
     */
    public function init(string $lang = 'fr', ?array $data = [])
    {
        $data = (!empty($data)) ? $data : (new Request)->data;
        $this->validator = new Validator($data, null, $lang);
    }
    /**
     * Reset object properties
     */
    public function clean()
    {
        $this->validator->reset();
    }


    /**
     * Validate that a field was "accepted" (based on PHP's string evaluation rules)
     * This validation rule implies the field is "required"
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function accept($fields) : self 
    {
        return $this->rule('accepted', $fields);
    }

    /**
     * Validate that a field contains only alphabetic characters
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function alpha($fields) : self 
    {
        return $this->rule('alpha', $fields);
    }
    /**
     * Validate that a field contains only alpha-numeric characters
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function alphaNum($fields) : self 
    {
        return $this->rule('alphaNum', $fields);
    }

    /**
     * Validate a field if it contains a given string
     * 
     * @param string|string[] $fields
     * @param string|string[] $values
     * @param bool $case_sensitive
     * @return dF_Validator
     */
    public function contains($fields, $values, $case_sensitive = false) : self 
    {
        $datas = array_combine((array) $fields, (array) $values);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('contains', $key, $value, $case_sensitive);
        }
        return $this;
    }

    /**
     * Validate that a field is a valid date
     * 
     * @param string|string[] $fields
     * @return  dF_validator
     */
    public function date($fields) : self 
    {
        return $this->rule('date', $fields);
    }
    /**
     * Validate the date is after a given date
     * 
     * @param string|array $fields
     * @return  dF_validator
     */
    public function dateAfter($fields) : self 
    {
        return $this->rule('dateAfter', $fields);
    }
    /**
     * Validate the date is before a given date
     * 
     * @param string|array $fields
     * @return  dF_validator
     */
    public function dateBefore($fields) : self 
    {
        return $this->rule('dateBefore', $fields);
    }
    /**
     * Validate that a field matches a date format
     * 
     * @param string|string[] $fields
     * @param string|string[] $format
     * @return dF_Validator 
     */
    public function dateFormat($fields, $format) : self
    {
        $datas = array_combine((array) $fields, (array) $format);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('dateFormat', $key, $value);
        }
        return $this;
    }

    /**
     * Validate that a field is different from another field
     * 
     * @param string|string[] $field1
     * @param string|string[] $field2
     * @return dF_Validator
     */
    public function different($field1, $field2) : self
    {
        $datas = array_combine((array) $field1, (array) $field2);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('different', $key, $value);
        }
        return $this;
    }
    /**
     * Validate that two values match
     * 
     * @param string|string[] $field1
     * @param string|string[] $field2
     * @return dF_Validator
     */
    public function equals($field1, $field2) : self 
    {
        $datas = array_combine((array) $field1, (array) $field2);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('equals', $key, $value);
        }
        return $this;
    }

    /**
     * Validate that a field is a valid e-mail address
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function email($fields) : self 
    {
        return $this->rule('email', $fields);
    }
    /**
     * Validate that a field is a valid e-mail address and the domain name is active
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function emailDNS($fields) : self 
    {
        return $this->rule('emailDNS', $fields);
    }

    /**
     * Validate a field if it's contained within a list of values
     * 
     * @param string|string[] $fields
     * @param array|array[array] $values
     * @return dF_Validator
     */
    public function in($fields, array $values) : self
    {
        $datas = array_combine((array) $fields, $values);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('in', $key, (array)$value);
        }
        return $this;
    }
    /**
     * Validate a field if it's not contained within a list of values
     * 
     * @param string|string[] $fields
     * @param array|array[array] $values
     * @return dF_Validator
     */
    public function notIn($fields, array $values) : self
    {
        $datas = array_combine((array) $fields, $values);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('notIn', $key, (array)$value);
        }
        return $this;
    }

    /**
     * Validate that a field is a valid IP address
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function ip($fields) : self 
    {
        return $this->rule('ip', $fields);
    }
    /**
     * Validate that a field is a valid IP v4 address
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function ipv4($fields) : self 
    {
        return $this->rule('ipv4', $fields);
    }
    /**
     * Validate that a field is a valid IP v6 address
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function ipv6($fields) : self 
    {
        return $this->rule('ipv6', $fields);
    }

    /**
     * Validate that a field if is an integer
     * 
     * @param string|string[] $fields
     * @param bool|null $negative Allow for integers to be supplied as negative values
     * @return  dF_validator
     */
    public function integer($fields, $negative = null) : self 
    {
        return $this->rule('integer', $fields, $negative);
    }
    /**
     * Validate that a field is numeric
     * 
     * @param string|string[] $fields
     * @return  dF_validator
     */
    public function numeric($fields) : self 
    {
        return $this->rule('numeric', $fields);
    }

    /**
     * Validate the length of a string
     * 
     * @param string|string[] $fields
     * @param int|int[] $lengths
     */
    public function length($fields, $lengths) : self
    {
        $datas = array_combine((array) $fields, (array) $lengths);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('length', $key, $value);
        }
        return $this;
    }
    /**
     * Validate the length of a string (between)
     * 
     * @param string|string[] $fields
     * @param int|array[int[]] $lengths
     */
    public function lengthBetween($fields, $lengths) : self
    {
        $datas = array_combine((array) $fields, (array) $lengths);
        foreach($datas As $key => $value)
        {
            $value = (array) $value;
            $value[1] = $value[1] ?? $value[0];
            $this->validator->rule('lengthBetween', $key, $value[0], $value[1]);
        }
        return $this;
    }/**
     * Validate the length of a string (max)
     * 
     * @param string|string[] $fields
     * @param int|int[] $lengths
     */
    public function lengthMax($fields, $lengths) : self
    {
        $datas = array_combine((array) $fields, (array) $lengths);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('lengthMax', $key, $value);
        }
        return $this;
    }
    /**
     * Validate the length of a string (min)
     * 
     * @param string|string[] $fields
     * @param int|int[] $lengths
     */
    public function lengthMin($fields, $lengths) : self
    {
        $datas = array_combine((array) $fields, (array) $lengths);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('lengthMin', $key, $value);
        }
        return $this;
    }

    /**
     * Validate the size of a field is less than a maximum value
     * 
     * @param string|string[] $fields
     * @param int|int[] $values
     */
    public function max($fields, $values) : self 
    {
        $datas = array_combine((array) $fields, (array) $values);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('max', $key, $value);
        }
        return $this;
    }
    /**
     * Validate the size of a field is greater than a minimum value
     * 
     * @param string|string[] $fields
     * @param int|int[] $values
     */
    public function min($fields, $values) : self 
    {
        $datas = array_combine((array) $fields, (array) $values);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('min', $key, $value);
        }
        return $this;
    }

    /**
     * Validate required field
     * 
     * @param string|string[] $fields
     * @param bool|null $exist Check if a field exist in data array
     * @return  dF_validator
     */
    public function required($fields, $exist = null) : self
    {
        return $this->rule('required', $fields, $exist);
    }
    /**
     * Validates whether or not a field is required based on whether or not other fields are present
     * 
     * @param string|string[] $fields
     * @param string|array[string|string[]] $others
     */
    public function requiredWith($fields, $others) : self 
    {
        $datas = array_combine((array) $fields, (array) $others);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('requiredWith', $key, $value);
        }
        return $this;
    }
    /**
     * Validates whether or not a field is required based on whether or not other fields are present
     * 
     * @param string|string[] $fields
     * @param string|array[string|string[]] $others
     */
    public function requiredWithout($fields, $others) : self 
    {
        $datas = array_combine((array) $fields, (array) $others);
        foreach($datas As $key => $value)
        {
            $this->validator->rule('requiredWithout', $key, $value);
        }
        return $this;
    }
    /**
     * Validate optional field
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function optional($fields) : self 
    {
        return $this->rule('optional', $fields);
    }
    
    /**
     * Validate that a field contains only alpha-numeric characters, dashes, and underscores
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function slug($fields) : self 
    {
        return $this->rule('slug', $fields);
    }

    /**
     * Validate that a field is a valid URL by syntax
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function url($fields) : self 
    {
        return $this->rule('url', $fields);
    }
    /**
     * Validate that a field is an active URL by verifying DNS record
     * 
     * @param string|string[] $fields
     * @return dF_Validator
     */
    public function urlActive($fields) : self 
    {
        return $this->rule('urlActive', $fields);
    }


    /**
     * Add validation rule(s) by field
     *
     * @param string $field
     * @param array  $rules
     * @return dF_Validator
     */
    public function addFieldRules(string $field, array $rules) : self
    {
        $this->validator->mapFieldRules($field, $rules);
        return $this;
    }
    /**
     * Add validation rule(s) for multiple fields
     *
     * @param array $rules
     * @return dF_Validator
     */
    public function addFieldsRules(array $rules) : self
    {
        $this->validator->mapFieldsRules($rules);
        return $this;
    }

    /**
     * Add labels to rules
     * 
     * @param array $labels
     * @return dF_Validator
     */
    public function labels(array $labels = []) : self
    {
        $this->validator->labels($labels);
        return $this;
    }
    /**
     * Add a specific label to current rule
     * 
     * @param string $value
     * @return dF_Validator
     */
    public function label(string $value) : self
    {
        $this->validator->label($value);
        return $this;
    }
    /**
     * Specify validation message to use for error for the last validation rule
     * 
     * @param string $message
     * @return dF_Validator
     */
    public function message(string $message) : self 
    {
        $this->validator->message($message);
        return $this;
    }

    /**
     * Add a single validation rule
     * 
     * @param string|callable $rule
     * @param string|array $fields
     * @return dF_Validator
     */
    public function rule($rule, $fields) : self
    {
        call_user_func_array([$this->validator, 'rule'], func_get_args());
        return $this;
    }
    /**
     * Add a multiple validation rules with an array
     * 
     * @param array $rules
     * @return dF_Validator
     */
    public function rules(array $rules) : self 
    {
        $this->validator->rules($rules);
        return $this;
    }

    /**
     * Run validation and return a boolean result
     * 
     * @return bool
     */
    public function validate() : bool
    {
        return $this->validator->validate();
    }
    /**
     * Get array of errors messages
     * 
     * @param string|null $field
     * @param array|bool
     */
    public function errors(?string $field = null)
    {
        return $this->validator->errors($field);
    }
}