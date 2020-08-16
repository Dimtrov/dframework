<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019, Dimtrov Sarl
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright	Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.2
 */

namespace dFramework\libraries;

use dFramework\core\loader\Service;
use dFramework\core\security\Csrf;
use dFramework\core\utilities\Tableau;

/**
 * Form
 *
 * Generateur de formulaire html a la volee
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/Form.html
 * @since       2.1
 * @file        /system/libraries/Form.php
 */
class Form
{   
    /**
     * @var array Donnees de formulaire
     */ 
    protected $datas = [];
    /**
     * @var array Erreurs enregistrees dans le formulaire
     */
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
     * @param mixed|null $value
     * @return dF_Form
     */
    public function value($key, $value = null) : self
    {
        if (is_array($key))
        {
            foreach ($key As $k => $v) 
            {
                $this->value($k, $v);
            }
        }
        if (is_string($key))
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
     * @return Form
     */
    public function error($key, $value = null) : self
    {
        if (is_array($key))
        {
            foreach ($key As $k => $v) 
            {
                $this->error($k, $v);
            }
        }
        if (is_string($key))
        {
            $this->errors[$key] = $value;
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
        if (false === $start OR false === $end)
        {
            $this->surround = [
                'start' => '',
                'end'   => ''
            ];
        }
        else if (null === $start OR null === $end)
        {
            $this->surround = [
                'start' => '<div class="form-group">',
                'end'   => '</div>'
            ];
        }
        else if (is_string($start) AND is_string($end))
        {
            $this->surround = compact('start', 'end');
        }
    }


    /**
     * Ouvre un formulaire en inserant une cle de securite
     *
     * @param string $action
     * @param string $method
     * @param int|false $token_time 
     * @param string|null $key
     * @param string|null $enctype
     * @param array|null $attributes
     * @return string
     */
    public function open(string $action, string $method = 'post', $token_time = 5, ?string $key = null, ?string $enctype = null, ?array $attributes = []) : string
    {
        $key = (!empty($key)) ? $key : 'form'.uniqid();
        $enctype = (!empty($enctype)) ? 'enctype="'.$enctype.'"' : '';
        $class = preg_replace('#form-control#i', 'form', $this->getInputClass($key, $attributes['class'] ?? null));

        $token = '';
        if (is_int($token_time))
        {
            $token = Csrf::instance()->generateToken($token_time, 20);
            $token = '<input type="hidden" name="formcsrftoken" value="'.$token.'" />';
        }
        
        return <<<HTML
            <form method="{$method}" action="{$action}" {$enctype} id="{$key}" class="{$class}" role="form" {$this->getAttributes($attributes)}>
                {$token}
HTML;
    }
    /**
     * Ferme un formulaire
     *
     * @return string
     */
    public function close() : string
    {
        return <<<HTML
            </form>
HTML;
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
        $r = $this->input('hidden', $key, false);
        $this->surround(null);
        return $r;
    }
    /**
     * Creer un input de type password
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function password(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('password', $key, $label, $attributes);
    }
    /**
     * Creer un input de type tel
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function tel(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('tel', $key, $label, $attributes);
    }
    /**
     * Creer un input de type email
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function email(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('email', $key, $label, $attributes);
    }
    /**
     * Creer un input de type url
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function url(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('url', $key, $label, $attributes);
    }
    /**
     * Creer un input de type search
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function search(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('search', $key, $label, $attributes);
    }
    /**
     * Creer un input de type number
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function number(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('number', $key, $label, $attributes);
    }
    /**
     * Creer un input de type range
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function range(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('range', $key, $label, Tableau::merge($attributes, ['class' => 'form-control-range']));
    }
    /**
     * Creer un input de type color
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function color(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('color', $key, $label, $attributes);
    }
    /**
     * Creer un input de type date
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function date(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('date', $key, $label, $attributes);
    }
    /**
     * Creer un input de type time
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function time(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('time', $key, $label, $attributes);
    }
    /**
     * Creer un input de type datetime
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function datetime(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('datetime', $key, $label, $attributes);
    }
    /**
     * Creer un input de type datetime-local
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function datetimeLocal(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('datetime-local', $key, $label, $attributes);
    }
    /**
     * Creer un input de type month
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function month(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('month', $key, $label, $attributes);
    }
    /**
     * Creer un input de type week
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function week(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('week', $key, $label, $attributes);
    }
    /**
     * Creer un input de type file
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function file(string $key, $label = null, ?array $attributes = []) : string 
    {
        return $this->input('file', $key, $label, Tableau::merge($attributes, ['class' => 'custom-file-input']));
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
        $key = $this->makeKey($key);
        $type = strtolower(($type));
        $value = ($type == 'password') ? null : 'value="'.$this->getValue($key).'"';
		
		return <<<HTML
            {$this->surround['start']}
                {$this->getLabel($key, $label, isset($attributes['required']))}
                <input type="{$type}" name="{$key}" id="field_{$key}" {$value} class="{$this->getInputClass($key, $attributes['class'] ?? null)}" {$this->getAttributes($attributes)} />
                {$this->getErrorFeedback($key)}
            {$this->surround['end']}
HTML;
    }

    /**
     * Cree un bouton de type submit
     *
     * @param string $value Valeur du bouton
     * @param array|null $attributes Attributs supplementaire
     * @param string|null $key Nom du champ
     * @return string
     */
    public function submit(string $value, ?array $attributes = [], ?string $key = null) : string
    {
        return $this->button($value, $attributes, $key, 'submit');
    }
    /**
     * Cree un bouton de type reset
     *
     * @param string $value Valeur du bouton
     * @param array|null $attributes Attributs supplementaire
     * @param string|null $key Nom du champ
     * @return string
     */
    public function reset(string $value, ?array $attributes = [], ?string $key = null) : string
    {
        return $this->button($value, $attributes, $key, 'reset');
    }
    /**
     * Cree un bouton de type image
     *
     * @param string $value Valeur du bouton
     * @param array|null $attributes Attributs supplementaire
     * @param string|null $key Nom du champ
     * @return string
     */
    public function image(string $value, ?array $attributes = [], ?string $key = null) : string
    {
        return $this->button($value, $attributes, $key, 'image');
    }

    /**
     * Cree un bouton
     *
     * @param string $value Valeur du bouton
     * @param array|null $attributes Attributs supplementaire
     * @param string|null $key Nom du champ
     * @param string|null $type Type d'input a creer
     * @return string
     */
    public function button(string $value, ?array $attributes = [], ?string $key = null, ?string $type = 'button') : string 
    {
        $type = (empty($type)) ? 'button' : strtolower($type);

        $value = ucfirst($value);
        $id = $this->makeKey($key !== null ? $key : $value);
        $name = ($key !== null) ? "name={$key}" : "";

        $class = $attributes['class'] ?? '';

        if (empty($attributes['class']) OR (!empty($attributes['class']) AND !preg_match('#btn-(primary|danger|default|secondary|warning|success|info)#i', strtolower($attributes['class']))))
        {
            switch (strtolower($type)) {
                case 'submit':
                    $class .= ' btn-primary';
                    break;
                case 'reset': 
                    $class .= ' btn-danger';
                    break;  
                default:
                    $class .= ' btn-default';
                    break;
            }
        }

        return <<<HTML
            {$this->surround['start']}
                <button type="{$type}" class="btn {$class}" id="btn_{$id}" {$name} {$this->getAttributes($attributes)}>{$value}</button>
            {$this->surround['end']}
HTML;
    }
    
    /**
     * Cree une zone de texte multiligne (textarea)
     *
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function textarea(string $key, $label = null, ?array $attributes = []) : string 
    {
        $key = $this->makeKey($key);

        return <<<HTML
            {$this->surround['start']}
                {$this->getLabel($key, $label, isset($attributes['required']))}
                <textarea name="{$key}" id="field_{$key}" class="{$this->getInputClass($key, $attributes['class'] ?? null)}" {$this->getAttributes($attributes)}>{$this->getValue($key)}</textarea>
                {$this->getErrorFeedback($key)}
            {$this->surround['end']}
HTML;
    }
    
    /**
     * Cree un liste d'option (select)
     *
     * @param string $key Nom du champ
     * @param array $options Options de la liste
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array|null $attributes Attributs supplementaire
     * @return string
     */
    public function select(string $key, array $options, $label = null, ?array $attributes = []) : string
    {
        $key = $this->makeKey($key);
        $r = '';
        foreach ($options As $k => $v)
        {
            if (is_array($v)) 
            {
                $r .= '<optgroup label="'.ucfirst($k).'">';
                foreach ($v As $cle => $valeur) 
                {
                    $valeur = (string) $valeur;
                    if (!is_string(($cle)))
                    {
                        $cle = $valeur;
                    }
                    if ($cle[0] === '_') 
                    {
                        $tmp = substr($cle, 1);
                        if (is_numeric($tmp))
                        {
                            $cle = $tmp;
                        }
                    } 
                    $selected = (strtolower($cle) == strtolower($this->getValue($key))) ? 'selected="selected"' : '';
                    $r .= '<option value="'.$cle.'" '.$selected.'>'.ucfirst($valeur).'</option>';   
                }
                $r .= '</optgroup>';
            }
            else 
            {
                $v = (string) $v;
                if (!is_string(($k)))
                {
                    $k = $v;
                }
                if ($k[0] === '_') 
                {
                    $tmp = substr($k, 1);
                    if (is_numeric($tmp))
                    {
                        $k = $tmp;
                    }
                }
                $selected = (strtolower($k) == strtolower($this->getValue($key))) ? 'selected="selected"' : '';
                $r .= '<option value="'.$k.'" '.$selected.'>'.ucfirst($v).'</option>';
            }
        }
        return <<<HTML
            {$this->surround['start']}
                {$this->getLabel($key, $label, isset($attributes['required']))}
                <select name="{$key}" id="field_{$key}" class="{$this->getInputClass($key, $attributes['class'] ?? null)}" {$this->getAttributes($attributes)}>{$r}</select>
                {$this->getErrorFeedback($key)}
            {$this->surround['end']}
HTML;
    }

    /**
     * Cree des cases a cocher (checkbox)
     *
     * @param string $key Nom du champ
     * @param array $options Options de la liste
     * @param array|null $attributes Attributs supplementaire
     * @param array|null $checked Cases qui seront automatiquement cochées
     * @param bool|null $only Specifie si les elements a cocher doivent etre dans des blocs independants ou pas
     * @return string
     */
    public function checkbox(string $key, array $options, ?array $attributes = [], $checked = null, ?bool $only = false) : string
    {
        $key = $this->makeKey($key);
        $r = ''; $i = 0;
        $class = preg_replace('#form-control#i', 'form-check-input', $this->getInputClass($key, $attributes['class'] ?? null));

        $surround_start = preg_replace('#form-group#i', 'form-check', $this->surround['start']);

        foreach ($options As $k => $v)
        {
            $i++;
            $v = (string) $v;
            if (!is_string(($k)))
            {
                $k = $v;
            }
            if ($k[0] === '_') 
            {
                $tmp = substr($k, 1);
                if (is_numeric($tmp))
                {
                    $k = $tmp;
                }
            }
            
            $checked = (in_array($k, (array) $checked) OR $k == $this->getValue($key)) ? 'checked="checked"' : '';

            if (true === $only)
            {
                $r .= $surround_start;
            }
            $r .= '<input type="checkbox" name="'.$key.'[]" id="field_'.$key.$i.'" class="'.$class.'" value="'.$k.'" '.$checked.' '.$this->getAttributes($attributes).'/>';
            $r .= '<label class="form-check-label" for="field_'.$key.$i.'">'.ucfirst($v).'</label>';
            if (true === $only)
            {
                $r .= $this->surround['end'];
            }
            $r .= "\n";
        }

        if (true === $only)
        {
            return <<<HTML
                {$r}
                {$this->getErrorFeedback($key)}
HTML;
        }
        return <<<HTML
            {$surround_start}
                {$r}
                {$this->getErrorFeedback($key)}
            {$this->surround['end']}
HTML;
    }

    /**
     * Cree des boutton radios (radio)
     *
     * @param string $key Nom du champ
     * @param array $options Options de la liste
     * @param array|null $attributes Attributs supplementaire
     * @param array|null $checked Cases qui seront automatiquement cochées
     * @param bool|null $only Specifie si les elements a cocher doivent etre dans des blocs independants ou pas
     * @return string
     */
    public function radio(string $key, array $options, ?array $attributes = [], $checked = null, ?bool $only = false) : string
    {
        $key = $this->makeKey($key);
        $r = ''; 
        $i = 0;
        $class = preg_replace('#form-control#i', 'form-check-input', $this->getInputClass($key, $attributes['class'] ?? null));
        
        $surround_start = preg_replace('#form-group#i', 'form-check', $this->surround['start']);
        
        foreach ($options As $k => $v)
        {
            $i++;
            $v = (string) $v;
            if (!is_string(($k)))
            {
                $k = $v;
            }
            if ($k[0] === '_') 
            {
                $tmp = substr($k, 1);
                if (is_numeric($tmp))
                {
                    $k = $tmp;
                }
            }
            
            $checked = (in_array($k, (array) $checked) OR $k == $this->getValue($key)) ? 'checked="checked"' : '';

            if (true === $only)
            {
                $r .= $surround_start;
            }
            $r .= '<input type="radio" name="'.$key.'" id="field_'.$key.$i.'" class="'.$class.'" value="'.$k.'" '.$checked.' '.$this->getAttributes($attributes).'/>';
            $r .= '<label class="form-check-label" for="field_'.$key.$i.'">'.ucfirst($v).'</label>';
            if (true === $only)
            {
                $r .= $this->surround['end'];
            }
            $r .= "\n";
        }

        if (true === $only)
        {
            return <<<HTML
                {$r}
                {$this->getErrorFeedback($key)}
HTML;
        }
        return <<<HTML
            {$surround_start}
                {$r}
                {$this->getErrorFeedback($key)}
            {$this->surround['end']}
HTML;
    }


    

    /**
     * Renvoie l'erreur relatif a un champ
     *
     * @param string $key Cle du champ dont on veut avoir l'erreur potentielle
     * @return string
     */
    protected function getErrorFeedback(string $key) : string
    {
        return (!isset($this->errors[$key])) ? '' : '<div class="invalid-feedback">' . implode('<br>', (array) $this->errors[$key]) . '</div>';
    }
    /**
     * Renvoie la/les classe(s) d'un input
     *
     * @param string $key Cle du champ en question
     * @param string|null $class Classe(s) a compiler
     * @return string
     */
    protected function getInputClass(string $key, ?string $class = '') : string
    {        
        $inputClass = Tableau::merge(explode(' ', $class), ['form-control']);
        if (isset($this->errors[$key])) 
        {
            $inputClass[] = 'is-invalid';
        }
        $inputClass = trim(implode(' ', $inputClass));

        return $inputClass;
    }
    /**
     * Renvoie le label d'un champ
     *
     * @param string $key Cle du champ dont on veut avoir le label
     * @param false|null|string $label Le label par default
	 * @param bool $required 
     * @return string
     */
    protected function getLabel(string $key, $label, bool $required = false) : string
    {
		$required = (true === $required) 
			? '<small class="text-danger">*</small>'
			: '';
        return (false === $label) ? '' : 
			'<label for="field_'.$key.'" class="form-label">'.ucfirst($label ?? $key).' '.$required.'</label>';
    }
    /**
     * Renvoie la valeur par defaut (predefinie) d'un champ de formulaire 
     *
     * @param string $key La cle du champ
     * @return string
     */
    protected function getValue(string $key) : string
    {
        $post = Service::request()->data[$key] ?? null;
        return (string) ( (!empty($post)) ? $post : ($this->datas[$key] ?? null) );
    }
    /**
     * Compile les autres attributs du champ
     *
     * @param array|null $attributes Les attributs a compiler
     * @return string
     */
    protected function getAttributes(?array $attributes) : string
    {
        if (!is_array($attributes))
        {
            return '';    
        }
        $reserved_attributes = ['type', 'name', 'class', 'id', 'value'];
        
        foreach ($reserved_attributes As $value)
        {
            $attributes = Tableau::remove($attributes, $value);
        }
        $return = '';

        foreach ($attributes As $key => $value) 
        {
            if (is_string($key))
            {
                $return .= ' '.$key.'="'.$value . '"';
            }
            if (is_int($key))
            {
                $return .= ' '.$value;
            }
        }
        return trim($return);
    }
    /**
     * Transforme et renvoi la cle d'un champ
     *
     * @param string $key
     * @return string
     */
    protected function makeKey(string $key = '') : string
    {
        if (empty($key))
        {
            return '';
        }
        return str_replace('\'', '', $key);
    }
}
