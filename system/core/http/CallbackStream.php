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
 * @homepage    https://dimtrov.hebfree.org/works/dframework
 * @version     3.2.2
 */

namespace dFramework\core\http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function array_key_exists;

use const SEEK_SET;

/**
 * CallbackStream
 *
 * Implementation of PSR HTTP streams
 *
 * @package		dFramework
 * @subpackage	Core
 * @category    Http
 * @author		Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/docs/dframework/api/
 * @since       3.2.2
 * @credit      CakeRequest (http://cakephp.org CakePHP(tm) Project)
 * @file        /system/core/http/CallbackStream.php
 */
class CallbackStream implements StreamInterface
{
    /**
     * @var callable|null
     */
    protected $callback;

    /**
     * @param callable $callback
     * @throws InvalidArgumentException
     */
    public function __construct(callable $callback)
    {
        $this->attach($callback);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->callback = null;
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $callback = $this->callback;
        $this->callback = null;
        return $callback;
    }

    /**
     * Attach a new callback to the instance.
     *
     * @param callable $callback
     * @throws InvalidArgumentException for callable callback
     */
    public function attach(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        throw new RuntimeException('Callback streams cannot tell position');
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return empty($this->callback);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        throw new RuntimeException('Callback streams cannot seek position');
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        throw new RuntimeException('Callback streams cannot rewind position');
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        throw new RuntimeException('Callback streams cannot write');
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        throw new RuntimeException('Callback streams cannot read');
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        $callback = $this->detach();
        $result = '';
        if (is_callable($callback)) {
            $result = $callback();
        }
        if (!is_string($result)) {
            return '';
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        $metadata = [
            'eof' => $this->eof(),
            'stream_type' => 'callback',
            'seekable' => false
        ];

        if (null === $key) {
            return $metadata;
        }

        if (! array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }
}
