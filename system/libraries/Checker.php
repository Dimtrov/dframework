<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.2
 */


/**
 * Checker
 *
 * The native dFramework datas checker
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Checker.html
 * @since       1.0
 * @file        /system/libraries/Checker.php
 */

class Checker
{
    /**
     * Les champs de donnees a utiliser pour les verifications
     *
     * @var array
     */
    private $field = [];
    /**
     * Specifie si on utilise les champs de donnees pour la verification ou pas
     *
     * @var boolean
     */
    private $use_input_field = false;

    /**
     * Definit les champs de donnees a utiliser pour la verification
     * 
     * @param bool $using
     * @param array $field
     */
    public function useInputField(bool $using, ?array $field = array())
    {
        $this->use_input_field = $using;
        $this->field = $field;
    }


    /**
     * Verifie si un nombre est compris entre une valeur minimale et une valeur maximale
     *
     * @param string|int|float $value Donnée à vérifier
     * @param int|float $min Nombre minimal récherché
     * @param int|float $max Nombre maximal récherché
     * @param bool $inclusive
     * @since 3.2
     * @return bool
     */
    public function between($value, $min, $max, bool $inclusive = false) : bool
    {
        $value = (true == is_string($value)) ? ($this->field[$value] ?? null) : $value;

        if (!$inclusive)
        {
             return ($value < $max AND $value > $min);
        }
        return ($value <= $max AND $value >= $min);
    }

    /**
     * Verifie si un champ existe(et n'est pas vide) parmi les champs utilisés par le validateur
     *
     * @param string ...$vars Liste des champs à vérifier l'existance
     * @return bool
     */
    public function inField(string ...$vars)
    {
        $status = true;
        foreach ($vars As $var)
        {
            if ($status === false)
            {
                break;
            }
            $status = !empty($this->field[$var]);
        }
        return $status;
    }

    /**
     * Verifie si une valeur existe dans un tableau ou une chaine separée par des point-virgules
     *
     * @param mixed $value Valeur que l'on souhaite verifier l'existance
     * @param array|string $array tableau de valeur ou chaine de valeurs séparées par des points-virgule
     * @return bool
     * @throws Exception
     */
    public function in($value, $array) : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null) : $value;
        if (is_string($array)) 
        {
            $array = explode(';', $array);
        }
        if (!is_array($array)) 
        {
            throw new Exception('Unsupported parameter to second argument. use a string or array variable');
        }
        return in_array($value, $array);
    }

    /**
     * Verifie si une donne ne contient que des caractere alphabetique ou que c'est un tableau qui n'a que des caractere aphabetique
     *
     * @param string|array $value Donnée à verifier
     * @return bool
     */
    public function is_alpha($value) : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null) : $value;
        if (is_array($value))
        {
            foreach ($value As $k => $v)
            {
                if (!is_string($v))
                {
                    return false;
                }
                $strlen = strlen($v);
                if ($strlen < 0 OR !preg_match("#^(?:[[:alpha:]]){".$strlen."}$#", $v))
                {
                    return false;
                }
            }
            return true;
        }
        if (is_string($value))
        {
            $strlen = strlen($value);
            return ($strlen > 0 AND preg_match("#^(?:[[:alpha:]]){".$strlen."}$#", $value));
        }
        return false;
    }
    /**
     * Verifie si une donne ne contient que des caractere alphanumerique ou que c'est un tableau qui n'a que des caractere aphanumerique
     *
     * @param string|array $value Donnée à verifier
     * @return bool
     */
    public function is_alphanum($value) : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null) : $value;
        if (is_array($value))
        {
            foreach ($value As $v)
            {
                if(!is_string($v))
                {
                    return false;
                }
                $strlen = strlen($v);
                if($strlen < 0 OR !preg_match("#^(?:[[:alnum:]]){".$strlen."}$#", $v))
                {
                    return false;
                }
            }
            return true;
        }
        if (is_string($value))
        {
            $strlen = strlen($value);
            return ($strlen > 0 AND preg_match("#^(?:[[:alnum:]]){".$strlen."}$#", $value));
        }
        return false;
    }

    /**
     * Verifie si une donnee est une date de naissance valide
     *
     * @param string $value Donnée à verifier
     * @param string $format Format de la date
     * @return bool
     */
    public function is_birthday($value, $format = 'dd/mm/yyyy') : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null) : $value;

        if (true !== $this->is_date($value, $format))
        {
            return false;
        }

        $date = self::formatDate($value, $format);
        rsort($date);
        $date = implode('-', $date);

        return ((new DateTime()) > (new DateTime($date)));
    }
    /**
     * Verifie si une donnee est une date valide
     *
     * @param string $value Donnée à verifier
     * @param string $format Format de la date
     * @return bool
     * @throws Exception
     */
    public function is_date($value, string $format = 'dd/mm/yyyy') : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null) : $value;

        list($day, $month, $year) = self::formatDate($value, $format);
        $day = (int) $day; $month = (int) $month; $year = (int) $year;

        if ($month < 1 OR $month > 12)
        {
            return false;
        }
        if (in_array($month, [1, 3, 5, 7, 8, 10, 12]))
        {
            return ($day >= 1 AND $day <= 31);
        }
        if ($month == 2)
        {
            return (($day >= 1 AND $day <= 28) OR ($day == 29 AND ($year % 4) == 0));
        }
        return ($day >= 1 AND $day <= 30);
    }

    /**
     * Verifie si une donnee est une adresse email valide
     *
     * @param mixed $value Donnée à vérifier
     * @return bool
     */
    public function is_email($value) : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null): $value;
        return !(filter_var($value, FILTER_VALIDATE_EMAIL) === false);
    }

    /**
     * Verifie si deux donnees sont egales ou identique
     *
     * @param mixed $a Donnée à vérifier
     * @param mixed $b Donnée à vérifier
     * @param bool $strict Spécifie si on utilise le mode strict (comparaison de type) pour la vérification
     * @return bool
     */
    public function is_equal($a, $b, $strict = false) : bool
    {
        $a = (true == $this->use_input_field) ? ($this->field[$a] ?? null) : $a;
        $b = (true == $this->use_input_field) ? ($this->field[$b] ?? null) : $b;

        if (true == $strict) 
        {
            return $a === $b;
        }
        return $a == $b;
    }

    /**
     * Verifie si une donnees correspond a une adresse ip valide
     *
     * @param $value Donnée à vérifier
     * @return bool
     */
    public function is_ip($value) : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null): $value;
        return !(filter_var($value, FILTER_VALIDATE_IP) === false);
    }

    /**
     * Verifie si une donnee est un nombre valide
     *
     * @param $value Donnée à vérifier
     * @return bool
     */
    public function is_number($value) : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null): $value;
        return is_numeric($value);
    }

    /**
     * Verifie si une donnees correspond a un numero valide
     *
     * @param mixed $value Donnée à vérifier
     * @param string|null $country Code ISO du pays d'appartenance du supposé numero
     * @param bool $use_indicatif Spécifie si on utilise l'indicatif du pays lors de la vérification
     * @return bool
     */
    public function is_tel($value, ?string $country = 'cm', bool $use_indicatif = true) : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null) : $value;

        $tel = trim(str_replace(['+','.',' ','-',',','_'], '', (string) $value));
        if (preg_match('/\D/', $tel))
        {
            return false;
        }
        $country = (empty($country)) ? $this->getCountryFromIndicatif($tel) : $country;
        $country = (empty($country)) ? 'cm' : strtolower($country);

        if ($country == 'cm')
        {
            $indicatif = (true === $use_indicatif) ? '\+?237\s?' : '';
            return (
                preg_match('#^'.$indicatif.'6\s?[5-9]{1}[0-9]{1}[-. ]?([0-9]{2}[-. ]?){3}$#', $tel) OR
                preg_match('#^'.$indicatif.'(2|3|4)\s?[2-3]{1}[0-9]{1}[-. ]?([0-9]{2}[-. ]?){3}$#', $tel)
            );
        }
        if ($country == 'fr')
        {
            $indicatif = (true === $use_indicatif) ? '\+?33\s?' : '';
            return preg_match('#^'.$indicatif.'0[1-68]([-. ]?[0-9]{2}){4}$#', $tel);
        }
        switch ($country)
        {
            case 'bj' : $indicatif = (true === $use_indicatif) ? '\+?229\s?' : ''; break;
            case 'ci' : $indicatif = (true === $use_indicatif) ? '\+?225\s?' : ''; break;
            case 'ml' : $indicatif = (true === $use_indicatif) ? '\+?223\s?' : ''; break;
            case 'sn' : $indicatif = (true === $use_indicatif) ? '\+?221\s?' : ''; break;
            case 'tg' : $indicatif = (true === $use_indicatif) ? '\+?\s?' : ''; break;
            default : $indicatif = ''; break;
        }
        return preg_match('#^'.$indicatif.'([-. ]?[0-9]+)+$#', $tel);
    }

    /**
     * Verifie si une donnee est une url
     *
     * @param $value Donnée à vérifier
     * @param bool $natif Spécifie si on utilise la filtre natif de PHP pour la verification ou pas
     * @return bool
     */
    public function is_url($value, bool $natif = false) : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null): $value;

        if ($natif === true)
        {
            return !(filter_var($value, FILTER_VALIDATE_URL) === false);
        }
        // Regex realisée par Scott Gonzalez: http://projects.scottsplayground.com/iri/
        /*
            Si vous voulez utilser ma propre regex, la voici -o)
            return preg_match('#^(https?|ftp|steam)://([a-z0-9._/-]+)$#i', $value);
        */
        return preg_match("/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i", $value);
    }

    /**
     * Verifie si un chaine a exactement un nombre de caracteres precis
     *
     * @param string $value Donnée à vérifier
     * @param int $length Longueur récherchée
     * @return bool
     */
    public function length(string $value, int $length) : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null) : $value;
        return strlen(trim($value)) == $length;
    }
    /**
     * Verifie si une chaine a une longueur comprise entre une valeur minimale et une valeur maximale
     *
     * @param string $value Donnée à vérifier
     * @param int $min Longueur minimale récherchée
     * @param int $max Longueur maximale récherchée
     * @param bool $inclusive
     * @return bool
     */
    public function length_between(string $value, int $min, int $max, bool $inclusive = false) : bool
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null) : $value;

        if (!$inclusive)
        {
             return (strlen(trim($value)) < $max AND strlen(trim($value)) > $min);
        }
        return (strlen(trim($value)) <= $max AND strlen(trim($value)) >= $min);
    }
    /**
     * @param $value Donnée à vérifier
     * @param int $length Longueur recherchée
     * @return bool
     */
    public function max_length(string $value, int $length)
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null) : $value;
        return strlen(trim($value)) <= $length;
    }
    /**
     * @param string $value Donnée à vérifier
     * @param int $length Longueur récherchée
     * @return bool
     */
    public function min_length(string $value, int $length)
    {
        $value = (true == $this->use_input_field) ? ($this->field[$value] ?? null) : $value;
        return strlen(trim($value)) >= $length;
    }


    /**
     * Verifie une données en utilisant un groupe de fonctions simultanément
     * 
     * @param $input L'entrée à vérifier
     * @param string $functions Les fonctions à utiliser (séparées par des pipes `|`)
     * @param callable|null $callback Le callback à exécuter à la fin de la vérification
     * @return mixed
     * @throws Exception
     */
    public function check($input, string $functions, ?callable $callback = null)
    {
        $status = true; $code = 0; $message = '';

        $functions = explode('|', $functions);
        foreach ($functions As $function)
        {
            if ($status === false) 
            {
                break;
            }
            $function = trim($function);

            if (preg_match('#^in\[(.+)\]$#isU', $function, $params))
            {
                $status = $this->in($input, $params[1]); 
                $code = 1;
                $message = ($status === true) ? 'Ok' : 'La donnée demandée n\'existe pas dans les entrées';
            }

            if (preg_match('#^is_birthday\[(.+)\]$#isU', $function, $params))
            {
                $status = $this->is_birthday($input, $params[1]); 
                $code = 2;
                $message = ($status === true) ? 'Ok' : 'Entrez une date de naissance valide';
            }
            if (preg_match('#^is_date\[(.+)\]$#isU', $function, $params))
            {
                $status = $this->is_date($input, $params[1]);
                $code = 3;
                $message = ($status === true) ? 'Ok' : 'Entrez une date valide';
            }

            if (preg_match('#^is_email$#isU', $function))
            {
                $status = $this->is_email($input); 
                $code = 4;
                $message = ($status === true) ? 'Ok' : 'Entrez une adresse email valide';
            }

            if (preg_match('#^is_equal\[(.+)\]$#isU', $function, $params))
            {
                $params[1] = explode(';', $params[1]);
                $status = $this->is_equal($input, $params[1][0], $params[1][1] ?? false);
                $code = 5;
                $message = ($status === true) ? 'Ok' : 'Les données ne correspondent pas';
            }

            if (preg_match('#^is_ip$#isU', $function))
            {
                $status = $this->is_ip($input);
                $code = 6;
                $message = ($status === true) ? 'Ok' : 'Entrez une adresse ip valide';
            }
			
            if (preg_match('#^is_tel\[(.+)\]$#isU', $function, $params))
            {
                $status = $this->is_tel($input, $params[1]);
                $code = 7;
                $message = ($status === true) ? 'Ok' : 'Entrez un numero de telephone valide';
            }
			
            if (preg_match('#^is_url\[(.+)\]$#isU', $function, $params))
            {
                $status = $this->is_url($input, (bool) $params[1]);
                $code = 8;
                $message = ($status === true) ? 'Ok' : 'Entrez une url valide';
            }

            if (preg_match('#^length\[(.+)\]$#isU', $function, $params))
            {
                $status = $this->length($input, $params[1]);
                $code = 9;
                $message = ($status === true) ? 'Ok' : 'Entrez un chaine ayant '.$params[1].' caracteres';
            }
            if (preg_match('#^max_length\[(.+)\]$#isU', $function, $params))
            {
                $status = $this->max_length($input, $params[1]);
                $code = 10;
                $message = ($status === true) ? 'Ok' : 'Entrez un chaine d\'au plus '.$params[1].' caracteres';
            }
            if (preg_match('#^min_length\[(.+)\]$#isU', $function, $params))
            {
                $status = $this->min_length($input, $params[1]);
                $code = 11;
                $message = ($status === true) ? 'Ok' : 'Entrez un chaine d\'au moins '.$params[1].' caracteres';
            }
        }

        if (null !== $callback AND is_callable($callback))
        {
            return call_user_func_array($callback, compact('status', 'code', 'message'));
        }
        return $status;
    }


    /**
     * Formatte une date et renvoi le jour, le mois et l'année
     *
     * @param string $date
     * @param string $format
     * @return array
     */
    private static function formatDate($date, string $format) : array
    {
        $format = strtoupper($format);

        if(in_array($format, ['DMY', 'DDMMYY']))
        {
            $day    = substr($date, 0, 2);
            $month  = substr($date, 2, 2);
            $year   = substr($date, 4, 2);
        }
        else if(in_array($format, ['D/M/Y', 'D:M:Y', 'D-M-Y', 'D_M_Y', 'D M Y', 'D.M.Y']))
        {
            $day    = substr($date, 0, 2);
            $month  = substr($date, 3, 2);
            $year   = substr($date, 6, 2);
        }
        else if(in_array($format, ['MDY', 'MMDDYY']))
        {
            $day    = substr($date, 2, 2);
            $month  = substr($date, 0, 2);
            $year   = substr($date, 4, 2);
        }
        else if(in_array($format, ['M/D/Y', 'M:D:Y', 'M-D-Y', 'M_D_Y', 'M D Y', 'M.D.Y']))
        {
            $day    = substr($date, 3, 2);
            $month  = substr($date, 0, 2);
            $year   = substr($date, 6, 2);
        }
        else if(in_array($format, ['YMD', 'YYMMDD']))
        {
            $day    = substr($date, 4, 2);
            $month  = substr($date, 2, 2);
            $year   = substr($date, 0, 2);
        }
        else if(in_array($format, ['Y/M/D', 'Y:M:D', 'Y-M-D', 'Y_M_D', 'Y M D', 'Y.M.D']))
        {
            $day    = substr($date, 6, 2);
            $month  = substr($date, 3, 2);
            $year   = substr($date, 0, 2);
        }
        else if(in_array($format, ['DDMMYYYY']))
        {
            $day    = substr($date, 0, 2);
            $month  = substr($date, 2, 2);
            $year   = substr($date, 4, 4);
        }
        else if(in_array($format, ['DD/MM/YYYY','DD:MM:YYYY','DD-MM-YYYY','DD_MM_YYYY','DD MM YYYY','DD.MM.YYYY']))
        {
            $day    = substr($date, 0, 2);
            $month  = substr($date, 3, 2);
            $year   = substr($date, 6, 4);
        }
        else if(in_array($format, ['MMDDYYYY']))
        {
            $day    = substr($date, 2, 2);
            $month  = substr($date, 0, 2);
            $year   = substr($date, 4, 4);
        }
        else if(in_array($format, ['MM/DD/YYYY', 'MM:DD:YYYY', 'MM-DD-YYYY', 'MM_DD_YYYY', 'MM DD YYYY', 'MM.DD.YYYY']))
        {
            $day    = substr($date, 3, 2);
            $month  = substr($date, 0, 2);
            $year   = substr($date, 6, 4);
        }
        else if(in_array($format, ['YYYYMMDD']))
        {
            $day    = substr($date, 6, 2);
            $month  = substr($date, 4, 2);
            $year   = substr($date, 0, 4);
        }
        else if(in_array($format, ['YYYY/MM/DD','YYYY:MM:DD','YYYY-MM-DDDD','YYYY_MM_DD','YYYY MM DDDD','YYYY.MM.DD']))
        {
            $day    = substr($date, 8, 2);
            $month  = substr($date, 5, 2);
            $year   = substr($date, 0, 4);
        }
        else
        {
            throw new Exception('Unsupported type of format for the date. Please see the manual for more information');
        }

        return [$day, $month, $year];
    }

    /**
     * Tente de recuperer le code ISO d'un pays a partir d'un numero de telephone (L'indicatif doit être specifié)
     *
     * @param string $tel
     * @return string
     */
    private function getCountryFromIndicatif($tel) : string
    {
        if(preg_match('#^\+?237\s?([-. ]?[0-9]+)+$#', $tel))
        {
            return 'cm';
        }
        if(preg_match('#^\+?229\s?([-. ]?[0-9]+)+$#', $tel))
        {
            return 'bj';
        }
        if(preg_match('#^\+?225\s?([-. ]?[0-9]+)+$#', $tel))
        {
            return 'ci';
        }
        if(preg_match('#^\+?223\s?([-. ]?[0-9]+)+$#', $tel))
        {
            return 'ml';
        }
        if(preg_match('#^\+?221\s?([-. ]?[0-9]+)+$#', $tel))
        {
            return 'sn';
        }
        //if(preg_match('#^\+?\s?([-. ]?[0-9]+)+$#', $tel)) { return 'tg'; }
        if(preg_match('#^\+?33\s?([-. ]?[0-9]+)+$#', $tel))
        {
            return 'fr';
        }
        return '';
    }
}
