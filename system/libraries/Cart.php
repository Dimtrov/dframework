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
 * @version     3.2
 */


/**
 * Cart
 *  Librairie native de gestion de paniers electroniques
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/guide/Dom.html
 * @since       3.2
 * @credit      Cart library - By Sei Kan <seikan.dev@gmail.com> - https://github.com/seikan/Cart
 * @file        /system/librairies/Cart.php
 */

class dF_Cart
{
    /**
	 * An unique ID for the cart.
	 *
	 * @var string
	 */
	protected $cartId;

	/**
	 * Maximum item allowed in the cart.
	 *
	 * @var int
	 */
	protected $maxItem = 0;

	/**
	 * Maximum quantity of a item allowed in the cart.
	 *
	 * @var int
	 */
	protected $maxQuantity = 0;

	/**
	 * Enable or disable cookie.
	 *
	 * @var bool
	 */
	protected $useCookie = false;

	/**
	 * A collection of cart items.
	 *
	 * @var array
	 */
	private $items = [];

	/**
	 * Initialize cart.
	 *
	 * @param array $options
	 */
	public function init(array $options = [])
	{
        if (isset($options['maxItem']) AND preg_match('/^\d+$/', $options['maxItem'])) 
        {
			$this->maxItem = $options['maxItem'];
		}
        if (isset($options['maxQuantity']) AND preg_match('/^\d+$/', $options['maxQuantity'])) 
        {
			$this->maxQuantity = $options['maxQuantity'];
		}
        if (isset($options['useCookie']) AND true === $options['useCookie']) 
        {
			$this->useCookie = true;
        }
        
		$this->cartId = md5($_SERVER['HTTP_HOST'] ?? 'dF_SimpleCart') . '_cart';

		$this->read();
    }

    
    /**
	 * Add item to cart.
	 *
	 * @param string $id
	 * @param int    $quantity
	 * @param array  $attributes
	 * @return bool
	 */
	public function add($id, int $quantity = 1, array $attributes = []) : bool
	{
		$quantity = (preg_match('/^\d+$/', $quantity)) ? $quantity : 1;
		$attributes = (is_array($attributes)) ? array_filter($attributes) : [$attributes];
		$hash = md5(json_encode($attributes));

        if (count($this->items) >= $this->maxItem AND $this->maxItem != 0) 
        {
			return false;
        }
        
        if (isset($this->items[$id])) 
        {
            foreach ($this->items[$id] As $index => $item) 
            {
                if ($item['hash'] == $hash) 
                {
                    $this->items[$id][$index]['quantity'] += $quantity;
                    if ($this->maxQuantity < $this->items[$id][$index]['quantity'] AND $this->maxQuantity != 0)
                    {
                        $this->items[$id][$index]['quantity'] =  $this->maxQuantity;
                    }

					$this->write();

                    return true;
				}
			}
		}

		$this->items[$id][] = [
			'id'         => $id,
			'quantity'   => ($quantity > $this->maxQuantity AND $this->maxQuantity != 0) ? $this->maxQuantity : $quantity,
			'hash'       => $hash,
			'attributes' => $attributes,
		];

		$this->write();

        return true;
	}

    /**
	 * Update item quantity.
	 *
	 * @param string $id
	 * @param int    $quantity
	 * @param array  $attributes
	 * @return bool
	 */
	public function update($id, int $quantity = 1, array $attributes = []) : bool
	{
        $quantity = (preg_match('/^\d+$/', $quantity)) ? $quantity : 1;
        
        if ($quantity == 0) 
        {
			$this->remove($id, $attributes);
            
            return true;
		}

        if (isset($this->items[$id])) 
        {
			$hash = md5(json_encode(array_filter($attributes)));

            foreach ($this->items[$id] As $index => $item) 
            {
                if ($item['hash'] == $hash) 
                {
                    $this->items[$id][$index]['quantity'] = $quantity;
                    if ($this->maxQuantity < $this->items[$id][$index]['quantity'] && $this->maxQuantity != 0)
                    {
                        $this->items[$id][$index]['quantity'] = $this->maxQuantity;
                    }
					
					$this->write();
            
                    return true;
				}
			}
        }
        
		return false;
	}

    /**
	 * Remove item from cart.
	 *
	 * @param string $id
	 * @param array  $attributes
	 * @return bool
	 */
	public function remove($id, array $attributes = []) : bool
	{
        if (!isset($this->items[$id])) 
        {
			return false;
		}

        if (empty($attributes)) 
        {
			unset($this->items[$id]);

			$this->write();
        
            return true;
		}
		$hash = md5(json_encode(array_filter($attributes)));

        foreach ($this->items[$id] As $index => $item) 
        {
			if ($item['hash'] == $hash) {
				unset($this->items[$id][$index]);

				$this->write();
        
                return true;
			}
        }
        
		return false;
    }
    
    
    /**
	 * Get items in  cart.
	 *
	 * @return array
	 */
	public function getItems() : array
	{
		return $this->items;
	}

	/**
	 * Check if the cart is empty.
	 *
	 * @return bool
	 */
	public function isEmpty() : bool
	{
		return empty(array_filter($this->items));
	}

	/**
	 * Get the total of item in cart.
	 *
	 * @return int
	 */
	public function totalItem() : int
	{
		$total = 0;
        foreach ($this->items As $items) 
        {
            foreach ($items As $item) 
            {
				++$total;
			}
		}
		return $total;
	}

	/**
	 * Get the total of item quantity in cart.
	 *
	 * @return int
	 */
	public function totalQuantity() : int
	{
		$quantity = 0;
        foreach ($this->items As $items) 
        {
            foreach ($items As $item) 
            {
				$quantity += $item['quantity'];
			}
		}
		return $quantity;
	}

	/**
	 * Get the sum of a attribute from cart.
	 *
	 * @param string $attribute
	 * @return int
	 */
	public function totalAttribute($attribute = 'price') : int
	{
		$total = 0;
        foreach ($this->items As $items) 
        {
            foreach ($items As $item) 
            {
                if (isset($item['attributes'][$attribute])) 
                {
					$total += $item['attributes'][$attribute] * $item['quantity'];
				}
			}
		}
		return $total;
	}

	/**
	 * Remove all items from cart.
	 */
	public function clear()
	{
		$this->items = [];
		$this->write();
	}

	/**
	 * Check if a item exist in cart.
	 *
	 * @param string $id
	 * @param array  $attributes
	 *
	 * @return bool
	 */
	public function itemExists($id, array $attributes = []) : bool
	{
		$attributes = (is_array($attributes)) ? array_filter($attributes) : [$attributes];

        if (isset($this->items[$id])) 
        {
			$hash = md5(json_encode($attributes));
            foreach ($this->items[$id] As $item) 
            {
                if ($item['hash'] == $hash) 
                {
					return true;
				}
			}
		}
		return false;
	}

    /**
	 * Destroy cart session.
	 */
	public function destroy()
	{
		$this->items = [];

        if ($this->useCookie) 
        {
			setcookie($this->cartId, '', -1);
        } 
        else 
        {
			unset($_SESSION[$this->cartId]);
		}
	}

	/**
	 * Read items from cart session.
	 */
	private function read()
	{
        $this->items = json_decode(
            (true === $this->useCookie) ? $_COOKIE[$this->cartId] ?? '[]' : $_SESSION[$this->cartId] ?? '[]'
            , true
        );
	}

	/**
	 * Write changes into cart session.
	 */
	private function write()
	{
        if ($this->useCookie) 
        {
			setcookie($this->cartId, json_encode(array_filter($this->items)), time() + 604800);
        } 
        else 
        {
			$_SESSION[$this->cartId] = json_encode(array_filter($this->items));
		}
	}
}
