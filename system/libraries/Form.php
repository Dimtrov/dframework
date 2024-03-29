<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package	    dFramework
 * @author	    Dimitric Sitchet Tomkeu <devcode.dst@gmail.com>
 * @copyright	Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright	Copyright (c) 2019 - 2021, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license	    https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.4
 */

namespace dFramework\libraries;

use dFramework\core\loader\Service;
use dFramework\core\utilities\Arr;
use dFramework\core\utilities\Str;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Form
 *
 * Generateur de formulaire html a la volee
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
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

    protected $attach_errors = true;

    /**
	 * @var ServerRequestInterface
	 */
	private $request;


	public function __construct()
	{
		$this->request = Service::request();
	}

	/**
     * Initailise les donnees et les erreurs du formulaire
     *
	 * @param ServerRequestInterface|null $request
     * @param array|null $datas
     * @param array|null $errors
     * @return self
     */
    public function init(?ServerRequestInterface $request = null, ?array $datas = [], ?array $errors = []) : self
    {
        $this->datas = !empty($datas) ? $datas : [];
        $this->errors = !empty($errors) ? $errors : [];

		if (!empty($request) AND $request instanceof ServerRequestInterface)
		{
			$this->request = $request;
		}

        return $this;
    }
    /**
     * Specifie si les erreurs doivent etre join au formulaire
     *
     * @param boolean $value
     * @return self
     */
    public function attachErrors(bool $value) : self
    {
        $this->attach_errors = $value;

        return $this;
    }
    /**
     * Definie la valeur pour une cle donnee
     *
     * @param string|array $key
     * @param mixed|null $value
     * @return self
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
            $this->datas = Arr::merge($this->datas, [$key => $value]);
        }
        return $this;
    }
    /**
     * Alias of value() method for array datas
     *
     * @param array $values
     * @return self
     */
    public function values(array $values) : self
    {
        return $this->value($values);
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
     * Alias of error() method for array datas
     *
     * @param array $errors
     * @return self
     */
    public function errors(array $errors) : self
    {
        return $this->error($errors);
    }

    /**
     * Affiche les erreurs d'un champ
     *
     * @param string $key
     * @return string
     */
    public function err(string $key) : string
    {
        return (!isset($this->errors[$key])) ? '' : implode('<br>', (array) $this->errors[$key]);
    }


    /**
     * Definie les balises ouvrante et fermante qui entoureront le champ
     *
     * @param false|null|string $start
     * @param false|null|string $end
     * @return self
     */
    public function surround($start = null, $end = null) : self
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

        return $this;
    }


	/**
	 * Ouvre un formulaire en inserant une cle de securite
	 *
	 * @param array $options
	 * @param array $attributes
	 * @return string
	 */
    public function open(array $options = [], array $attributes = []) : string
    {
    	$options = array_merge([
    		'action'     => $this->request->getRequestTarget(),
    		'type'       => 'post',
    		'enctype'    => null,
    		'attributes' => []
		], $options);

    	$formAttributes = [];
    	$append = '';

    	switch (strtolower($options['type']))
		{
			case 'get':
				$formAttributes['method'] = 'get';
				break;
			case 'file':
				$formAttributes['enctype'] = 'multipart/form-data';
				$options['type'] = empty($this->datas) ? 'post' : 'put';
			// Move on
			case 'post':
				// Move on
			case 'put':
				// Move on
			case 'delete':
				// Set patch method
			case 'patch':
				$append .= $this->hidden('_method', [
					'name' => '_method',
					'secure' => 'skip',
					'value' => strtoupper($options['type'])
				]);
			// Default to post method
			default:
				$formAttributes['method'] = 'post';
		}
		if (isset($options['method']))
		{
			$formAttributes['method'] = strtolower($options['method']);
		}
		if (isset($options['enctype']))
		{
			$formAttributes['enctype'] = strtolower($options['enctype']);
		}
		if (!empty($options['encoding']))
		{
			$formAttributes['accept-charset'] = $options['encoding'];
		}

		if ('get' !== strtolower($options['type']))
		{
			$append .= $this->csrfField();
		}
		$options = array_merge($options, $formAttributes);

        $key = !empty($options['key']) ? $options['key'] : 'form'.uniqid();
        $enctype = !empty($options['enctype']) ? 'enctype="'.$options['enctype'].'"' : '';
        $attributes = array_merge((array) $options['attributes'], $attributes);

        $class = preg_replace('#form-control#i', 'form', $this->getInputClass($key, $attributes['class'] ?? null));

        return <<<HTML
            <form method="{$options['method']}" action="{$options['action']}" {$enctype} id="{$key}" class="{$class}" role="form" {$this->getAttributes($attributes)}>
                {$append}
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
	 * @param array|null $attributes
	 * @return string
	 */
    public function hidden(string $key, ?array $attributes = []) : string
    {
		unset($attributes['disabled']);
		$k = array_search('disabled', $attributes);
		if (is_string($k) OR is_int($k))
		{
			unset($attributes[$k]);
		}
        $this->surround(false);
        $r = $this->input('hidden', $key, false, $attributes);
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
        return $this->input('range', $key, $label, Arr::merge($attributes, ['class' => 'form-control-range']));
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
        return $this->input('file', $key, $label, Arr::merge($attributes, ['class' => 'custom-file-input']));
    }

    /**
     * Creer un input
     *
     * @param string $type Type d'input a creer
     * @param string $key Nom du champ
     * @param false|null|string $label Nom du label (si false pas de label, si null label issu du parametre key)
     * @param array $attributes Attributs supplementaire
     * @return string
     */
    public function input(string $type, string $key, $label = null, array $attributes = []) : string
    {
		$key = $this->makeKey($key);
		$attributes = $this->makeAttributes($attributes, $key);
        $type = strtolower($type);
        $name = $attributes['name'] ?? $key;

        $value = ($type == 'password') ? null : 'value="'.($attributes['value'] ?? $this->getValue($key)).'"';
		$description = ($type != 'hidden') ? $this->makeDescription($attributes, $key) : '';
		$error_feedback = ($type != 'hidden') ? $this->getErrorFeedback($key) : '';

		$hidden = '';
		if ((isset($attributes['disabled']) AND true == $attributes['disabled']) OR in_array('disabled', $attributes))
		{
			$hidden = $this->hidden($key, $attributes);
		}

		$prepend = $append = '';
		if ($type === 'file')
		{
            $placeholder = $attributes['placeholder'] ?? 'Choose file';
            unset($attributes['placeholder']);

			$prepend = '<div class="custom-file">';
			$append = '<label class="custom-file-label" for="field_'.$key.'">'.$placeholder.'</label></div>';
		}

		return <<<HTML
            {$this->surround['start']}
                {$this->getLabel($key, $label, in_array('required', array_values($attributes)))}
                {$prepend}<input type="{$type}" name="{$name}" id="field_{$key}" {$value} class="{$this->getInputClass($key, $attributes['class'] ?? null)}" {$this->getAttributes($attributes)} />{$append}
				{$description} {$error_feedback} {$hidden}
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
        $href = '';
		if (!empty($attributes['href']))
		{
			$href = $attributes['href'];
			unset($attributes['href']);
		}

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

        $btn = '<button type="'.$type.'" class="btn '.$class.'" id="btn_'.$id.'" '.$name.' '.$this->getAttributes($attributes).'>'.$value.'</button>';
		if ($type === 'reset' AND !empty($href))
		{
			$btn = '<a href="'.$href.'" class="btn '.$class.'" id="btn_'.$id.'" '.$name.' '.$this->getAttributes($attributes).'>'.$value.'</a>';
		}

        return <<<HTML
            {$this->surround['start']}
                {$btn}
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
		$attributes = $this->makeAttributes($attributes, $key);
        $name = $attributes['name'] ?? $key;

       	$hidden = '';
		if ((isset($attributes['disabled']) AND true == $attributes['disabled']) OR in_array('disabled', $attributes))
		{
			$hidden = $this->hidden($key, $attributes);
		}

        return <<<HTML
            {$this->surround['start']}
                {$this->getLabel($key, $label, in_array('required', array_values($attributes)))}
                <textarea name="{$name}" id="field_{$key}" class="{$this->getInputClass($key, $attributes['class'] ?? null)}" {$this->getAttributes($attributes)}>{$this->getValue($key)}</textarea>
                {$this->makeDescription($attributes, $key)} {$this->getErrorFeedback($key)} {$hidden}
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
		$attributes = $this->makeAttributes($attributes, $key);
        $name = $attributes['name'] ?? $key;

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
                    $selected = in_array($cle, $this->getValues($key)) ? 'selected="selected"' : '';
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
                $selected = in_array($k, $this->getValues($key)) ? 'selected="selected"' : '';
                $r .= '<option value="'.$k.'" '.$selected.'>'.ucfirst($v).'</option>';
            }
        }

        return <<<HTML
            {$this->surround['start']}
                {$this->getLabel($key, $label, in_array('required', array_values($attributes)))}
                <select name="{$name}" id="field_{$key}" class="{$this->getInputClass($key, $attributes['class'] ?? null)}" {$this->getAttributes($attributes)}>{$r}</select>
                {$this->makeDescription($attributes, $key)} {$this->getErrorFeedback($key)}
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
		$attributes = $this->makeAttributes($attributes, $key);
        $name = $attributes['name'] ?? $key;

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
            $r .= '<input type="checkbox" name="'.$name.'[]" id="field_'.$key.$i.'" class="'.$class.'" value="'.$k.'" '.$checked.' '.$this->getAttributes($attributes).'/>';
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
                {$this->makeDescription($attributes, $key)} {$this->getErrorFeedback($key)}
HTML;
        }
        return <<<HTML
            {$surround_start}
                {$r}
                {$this->makeDescription($attributes, $key)} {$this->getErrorFeedback($key)}
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
     * @param bool|null $inline Specifie si les elements a cocher doivent etre sur la meme ligne ou pas
     * @return string
     */
    public function radio(string $key, array $options, ?array $attributes = [], $checked = null, ?bool $inline = false) : string
    {
        $key = $this->makeKey($key);
		$attributes = $this->makeAttributes($attributes, $key);
        $name = $attributes['name'] ?? $key;

        $r = '';
        $i = 0;
        $class = preg_replace('#form-control#i', 'form-check-input', $this->getInputClass($key, $attributes['class'] ?? null));

        $new_class = 'form-check' . (true === $inline ? ' form-check-inline' : '');
        $surround_start = preg_replace('#form-group#i', $new_class, $this->surround['start']);

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

            $r .= $surround_start;

            $r .= '<input type="radio" name="'.$name.'" id="field_'.$key.$i.'" class="'.$class.'" value="'.$k.'" '.$checked.' '.$this->getAttributes($attributes).'/>';
            $r .= '<label class="form-check-label" for="field_'.$key.$i.'">'.ucfirst($v).'</label>';

            $r .= $this->surround['end'];

            $r .= "\n";
        }

        return <<<HTML
            {$r}
            {$this->makeDescription($attributes, $key)} {$this->getErrorFeedback($key)}
HTML;
    }



	/**
	 * Return a CSRF input if the request data is present.
	 * Used to secure forms in conjunction with CsrfComponent &
	 * SecurityComponent
	 *
	 * @return string
	 */
	protected function csrfField() : string
	{
		$options = config('data.csrf');
		$config = [
			'cookieName' => $options['cookie_name'] ?? 'csrfToken',
			'field'      => $options['token_name'] ?? '_csrfToken',
		];

		$token = $this->request->getAttribute($config['cookieName']);
		if (empty($token))
		{
			return '';
		}

		return $this->hidden($config['field'], [
			'value'        => $token,
			'secure'       => 'skip',
			'autocomplete' => 'off',
		]);
	}

    /**
     * Renvoie l'erreur relatif a un champ
     *
     * @param string $key Cle du champ dont on veut avoir l'erreur potentielle
     * @return string|null
     */
    protected function getErrorFeedback(string $key) : string
    {
        if ($this->attach_errors)
        {
            return (!isset($this->errors[$key])) ? '' : '<div class="invalid-feedback">' . implode('<br>', (array) $this->errors[$key]) . '</div>';
        }
        return '';
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
        $inputClass = Arr::merge(explode(' ', $class), ['form-control']);
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
		$text = Str::toSnake($label ?? $key);
		$text = str_replace('_', ' ', ucfirst($text));

		$required = (true === $required)
			? '<span class="text-danger">*</span>'
			: '';
        return (false === $label) ? '' :
			'<label for="field_'.$key.'" class="form-label">'.$text.' '.$required.'</label>';
    }

    /**
     * Renvoie la valeur par defaut (predefinie) d'un champ de formulaire
     *
     * @param string $key La cle du champ
     * @return array
     */
    protected function getValues(string $key) : array
    {
		$parsedBody = $this->request->getParsedBody();
		$post = (array) ($parsedBody[$key] ?? []);

        return (array) (!empty($post) ? $post : ($this->datas[$key] ?? []));
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getValue(string $key) : string
    {
        return (string) ($this->getValues($key)[0] ?? null);
    }

    /**
     * Compile les autres attributs du champ
     *
     * @param array|null $attributes Les attributs a compiler
     * @return string
     */
    protected function getAttributes(?array $attributes = null) : string
    {
        if (!is_array($attributes))
        {
            return '';
        }

        $reserved_attributes = ['type', 'name', 'class', 'id', 'value'];
        foreach ($reserved_attributes As $value)
        {
            $attributes = Arr::remove($attributes, $value);
        }

        $return = '';
        foreach ($attributes As $key => $value)
        {
			if ($key === 'description')
			{
				continue;
			}
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
        return str_replace('\'', '', Str::toSnake($key));
    }

	/**
	 * Cree les attributs necessaires à un champs de saisie
	 *
	 * @param array $attributes
	 * @param string $key
	 * @return array
	 */
	protected function makeAttributes(array $attributes, string $key) : array
	{
		return array_merge($attributes, [
			'aria-describedby' => 'help_field_'.$key
		]);
	}

	/**
	 * Genere la description d'un champ de saisie
	 *
	 * @param array $attributes
	 * @param string $key
	 * @return string
	 */
	protected function makeDescription(array $attributes, string $key) : string
	{
		$description = $attributes['description'] ?? null;
		if (empty($description))
		{
			return '';
		}
		return '<small id="help_field_'.$key.'" class="text-muted">'.$description.'</small>';
	}
}
