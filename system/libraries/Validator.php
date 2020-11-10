<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2020, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2020, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2020, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.2
 */

 namespace dFramework\libraries;
 
use dFramework\core\loader\Service;
use dFramework\core\output\Language;
use Valitron\Validator As Valitron;

/**
 * Validator
 *
 * The dFramework Form Validator
 *  Valid a form data based on vlucas/valitron dependancy
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Validator.html
 * @since       3.0
 * @file        /system/libraries/Validator.php
 */
class Validator
{
    /**
     * @var Valitron
     */
    private $validator = null;

    /**
     * Initialize validation process. Entry point of all 
     */
    public function init(string $lang = null, ?array $data = [])
    {
        if (empty($lang) OR $lang == 'auto')
        {
            $lang = Language::searchLocale();
        }

        if (empty($data)) 
        {
            $data = Service::request()->data;
        }

        $this->validator = new Valitron($data, null, $lang);


        $this->validator->addInstanceRule('tel', function($field, bool $use_indicatif = false) {
            $tel = $this->validator->data()[$field] ?? null;

            if (empty($tel)) 
            {
                return false;
            }
            $use_indicatif = func_get_arg(2)[0];

            return (new Checker)->is_tel($tel, null, $use_indicatif);
        }, '{field} that you enter is invalid');
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
     * @return self
     */
    public function accept($fields) : self 
    {
        return $this->rule('accepted', $fields);
    }

    /**
     * Validate that a field contains only alphabetic characters
     * 
     * @param string|string[] $fields
     * @return self
     */
    public function alpha($fields) : self 
    {
        return $this->rule('alpha', $fields);
    }
    /**
     * Validate that a field contains only alpha-numeric characters
     * 
     * @param string|string[] $fields
     * @return self
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
     * @return self
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
     * @param string|string[] $fields
     * @param string|string[] $date_value
     * @param bool $strict
     * @return  self
     */
    public function dateAfter($fields, $date_value = null, bool $strict = true) : self 
    {
        if ($strict === false)
        {
            $this->validator->addInstanceRule('personalDateAfter', function($field, $date) {
                $day = $this->validator->data()[$field] ?? null;
                $date = func_get_arg(2)[0];
                
                $vtime = ($day instanceof \DateTime) ? $day->getTimestamp() : strtotime($day);
                $ptime = ($date instanceof \DateTime) ? $date->getTimestamp() : strtotime($date);
    
                return $vtime >= $ptime;
            }, '{field} that you enter is invalid');
        }
        
        if (empty($date_value)) 
        {
            $date_value = date('Y-m-d H:i:s');
        }
        $datas = array_combine((array) $fields, (array) $date_value);
        
        foreach ($datas As $key => $value)
        {
            if (false === $strict)
            {
                $this->validator->rule('personalDateAfter', $key, $value);
            }
            else 
            {
                $this->validator->rule('dateAfter', $key, $value);
            }
        }

        return $this;
    }
    /**
     * Validate the date is before a given date
     * 
     * @param string|array $fields
     * @param string|string[] $date_value
     * @param bool $strict
     * @return  self
     */
    public function dateBefore($fields, $date_value = null, bool $strict = true) : self 
    {
        if ($strict === false)
        {
            $this->validator->addInstanceRule('personalDateBefore', function($field, $date) {
                $day = $this->validator->data()[$field] ?? null;
                $date = func_get_arg(2)[0];
                
                $vtime = ($day instanceof \DateTime) ? $day->getTimestamp() : strtotime($day);
                $ptime = ($date instanceof \DateTime) ? $date->getTimestamp() : strtotime($date);
    
                return $vtime <= $ptime;
            }, '{field} that you enter is invalid');
        }
        
        if (empty($date_value)) 
        {
            $date_value = date('Y-m-d H:i:s');
        }
        $datas = array_combine((array) $fields, (array) $date_value);
        
        foreach ($datas As $key => $value)
        {
            if (false === $strict)
            {
                $this->validator->rule('personalDateBefore', $key, $value);
            }
            else 
            {
                $this->validator->rule('dateBefore', $key, $value);
            }
        }

        return $this;
    }
    /**
     * Validate that a field matches a date format
     * 
     * @param string|string[] $fields
     * @param string|string[] $format
     * @return self 
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function email($fields) : self 
    {
        return $this->rule('email', $fields);
    }
    /**
     * Validate that a field is a valid e-mail address and the domain name is active
     * 
     * @param string|string[] $fields
     * @return self
     */
    public function emailDNS($fields) : self 
    {
        return $this->rule('emailDNS', $fields);
    }

    /**
     * Validate a field if it's contained within a list of values
     * 
     * @param string|string[] $fields
     * @param array|array[] $values
     * @return self
     */
    public function in($fields, array $values) : self
    {
        $this->validator->rule('in', $fields, $values);
        
        return $this;
    }
    /**
     * Validate a field if it's not contained within a list of values
     * 
     * @param string|string[] $fields
     * @param array|array[array] $values
     * @return self
     */
    public function notIn($fields, array $values) : self
    {
        $this->validator->rule('notIn', $fields, $values);
        
        return $this;
    }

    /**
     * Validate that a field is a valid IP address
     * 
     * @param string|string[] $fields
     * @return self
     */
    public function ip($fields) : self 
    {
        return $this->rule('ip', $fields);
    }
    /**
     * Validate that a field is a valid IP v4 address
     * 
     * @param string|string[] $fields
     * @return self
     */
    public function ipv4($fields) : self 
    {
        return $this->rule('ipv4', $fields);
    }
    /**
     * Validate that a field is a valid IP v6 address
     * 
     * @param string|string[] $fields
     * @return self
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
     * @return self
     */
    public function integer($fields, $negative = null) : self 
    {
        return $this->rule('integer', $fields, $negative);
    }
    /**
     * Validate that a field is numeric
     * 
     * @param string|string[] $fields
     * @return self
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
     * @return self
     */
    public function length($fields, $lengths) : self
    {
        $datas = $this->_combineArray((array) $fields, (array) $lengths);
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
     * @return self
     */
    public function lengthBetween($fields, $lengths) : self
    {
        $fields = (array) $fields;
        foreach($fields As $field)
        {
            $this->validator->rule('lengthBetween', $field, $lengths[0], $lengths[1] ?? $lengths[0]);
        }
        return $this;
    }
    /**
     * Validate the length of a string (max)
     * 
     * @param string|string[] $fields
     * @param int|int[] $lengths
     * @return self
     */
    public function lengthMax($fields, $lengths) : self
    {
        $datas = $this->_combineArray((array) $fields, (array) $lengths);
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
     * @return self
     */
    public function lengthMin($fields, $lengths) : self
    {
        $datas = $this->_combineArray((array) $fields, (array) $lengths);
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
     * @return self
     */
    public function max($fields, $values) : self 
    {
        $datas = $this->_combineArray((array) $fields, (array) $values);
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
     * @return self
     */
    public function min($fields, $values) : self 
    {
        $datas = $this->_combineArray((array) $fields, (array) $values);
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
     * @return self
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
     * @return self
     */
    public function requiredWith($fields, $others) : self 
    {
        $datas = $this->_combineArray((array) $fields, (array) $others);
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
     * @return self
     */
    public function requiredWithout($fields, $others) : self 
    {
        $datas = $this->_combineArray((array) $fields, (array) $others);
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
     * @return self
     */
    public function optional($fields) : self 
    {
        return $this->rule('optional', $fields);
    }
    
    /**
     * Validate that a field contains only alpha-numeric characters, dashes, and underscores
     * 
     * @param string|string[] $fields
     * @return self
     */
    public function slug($fields) : self 
    {
        return $this->rule('slug', $fields);
    }

    /**
     * Validate that a field is a valid URL by syntax
     * 
     * @param string|string[] $fields
     * @return self
     */
    public function url($fields) : self 
    {
        return $this->rule('url', $fields);
    }
    /**
     * Validate that a field is an active URL by verifying DNS record
     * 
     * @param string|string[] $fields
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function rules(array $rules) : self 
    {
        $this->validator->rules($rules);
        return $this;
    }

    /**
     * Ajoute une regle de validation pour les numero de telephone
     *
     * @param string $field
     * @param boolean $use_indicatif
     * @return self
     */
    public function tel(string $field, bool $use_indicatif = false) : self 
    {
        return $this->rule('tel', $field, $use_indicatif);
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


    /**
     * Petite adaptation de la fonction array_combine
     *
     * @param array $key
     * @param array $value
     * @return array
     */
    private function _combineArray(array $key, array $value) : array 
    {
        $nbr_val = count($value);
        
        foreach ($key As $entry) 
        {
            for($i = 0; $i < $nbr_val; $i++)
            {
                $key[$i] = $entry;
            }
        }

        return array_combine($key, $value);
    }
}