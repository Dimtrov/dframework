<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     2.1
 */

use dFramework\core\data\Request;
use dFramework\core\utilities\Tableau;

/**
 * dF_Form
 *
 * Generateur de formulaire html a la volee
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Form.html
 * @file        /system/libraries/Form.php
 */

class dF_Form
{   
 
    protected $datas = [];

    protected $errors = [];

    /**
     * @var array Balise d'entoutage des champs
     */
    protected $surround = [
        'start' => '<div class="form-group">',
        'end'   => '</div>'
    ];

    /**
     * Initailise les donnees et les erreurs du formulaire
     *
     * @param array|null $datas
     * @param array|null $errors
     * @return void
     */
    public function init(?array $datas = [], ?array $errors = []) : void
    {
        $this->datas = $datas;
        $this->errors = $errors;
    }
    /**
     * Definie la valeur pour une cle donnee
     *
     * @param string|array $key
     * @param mixed $value
     * @return dF_Form
     */
    public function value($key, $value) : self
    {
        if(is_array($key))
        {
            foreach($key As $k => $v) 
            {
                $this->value($k, $v);
            }
        }
        if(is_string($key))
        {
            $this->datas = Tableau::merge($this->datas, [$key => $value]);
        }
        return $this;
    }
    /**
     * Definie l'erreur pour une cle donnee
     *
     * @param string|array $key
     * @param mixed $value
     * @return dF_Form
     */
    public function error($key, $value) : self
    {
        if(is_array($key))
        {
            foreach($key As $k => $v) 
            {
                $this->error($k, $v);
            }
        }
        if(is_string($key))
        {
            $this->errors = Tableau::merge($this->errors, [$key => $value]);
        }
        return $this;
    }



    /**
     * Definie les balises ouvrante et fermante qui entoureront le champ
     *
     * @param false|null|string $start
     * @param false|null|string $end
     * @return void
     */
    public function surround($start = null, $end = null) : void
    {
        if(false === $start OR false === $end)
        {
            $this->surround = [
                'start' => '',
                'end'   => ''
            ];
        }
        else if(null === $start OR null === $end)
        {
            $this->surround = [
                'start' => '<div class="form-group">',
                'end'   => '</div>'
            ];
        }
        else if(is_string($start) AND is_string($end))
        {
            $this->surround = compact('start', 'end');
        }
    }


    /**
     * Creer un input de type text
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function text(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('text', $key, $label, $attributes);
    }

    /**
     * Cree un input de type hidden
     * 
     * @param string $key Nom du champ
     * @return string
     */
    public function hidden(string $key) : string
    {
        $this->surround(false);
        return $this->input('hidden', $key, false);
    }

    /**
     * Creer un input
     *
     * @param string $type Type d'input a creer
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function input(string $type, string $key, $label = null, ?array $attributes = []) : string 
    {
        return <<<HTML
            {$this->surround['start']}
                {$this->getLabel($key, $label)}
                <input type="{$type}" name="{$key}" id="field{$key}" class="{$this->getInputClass($key, $attributes['class'] ?? null)}" value="{$this->getValue($key)}" {$this->getAttributes($attributes)} />
                {$this->getErrorFeedback($key)}
            {$this->surround['end']}
HTML;
    }

    public function textarea(string $key, ?string $label = null) : string 
    {
        return '';
    }



    private function getErrorFeedback(string $key) : string
    {
        return (!isset($this->errors[$key])) ? '' : '<div class="invalid-feedback">' . implode('<br>', $this->errors[$key]) . '</div>';
    }
    private function getInputClass(string $key, ?string $class = '') : string
    {        
        $inputClass = Tableau::merge(explode(' ', $class), ['form-control']);
        if(isset($this->errors[$key])) 
        {
            $inputClass[] = 'is-invalid';
        }
        $inputClass = trim(implode(' ', $inputClass));

        return $inputClass;
    }
    
    protected function getLabel(string $key, $label) : string
    {
        return (false === $label) ? '' : '<label for="field'.$key.'" class="form-label">'.ucfirst((string)$label ?? $key).'</label>';
    }
    protected function getValue(string $key) : string
    {
        $post = (new Request)->data[$key] ?? null;
        return (string) ( (!empty($post)) ? $post : ($this->datas[$key] ?? null) );
    }
    /**
     * Compile les autres attributs du champ
     *
     * @param array $attributes Les attributs a compiler
     * @return string
     */
    protected function getAttributes(array $attributes) : string
    {
        $reserved_attributes = ['type', 'name', 'class', 'id', 'value'];
        foreach($reserved_attributes As $value)
        {
            $attributes = Tableau::remove($attributes, $value);
        }
        $return = '';

        foreach ($attributes As $key => $value) 
        {
            if(is_string($key))
            {
                $return .= ' '.$key.'="'.$value . '"';
            }
            if(is_int($key))
            {
                $return .= ' '.$value;
            }
        }
        return trim($return);
    }
}