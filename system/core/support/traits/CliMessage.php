<?php
/**
 * dFramework
 *
 * The simplest PHP framework for beginners
 * Copyright (c) 2019 - 2021, Dimtrov Lab's
 * This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package     dFramework
 * @author      Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @copyright   Copyright (c) 2019 - 2021, Dimtrov Lab's. (https://dimtrov.hebfree.org)
 * @copyright   Copyright (c) 2019 - 2021, Dimitri Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license     https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link        https://dimtrov.hebfree.org/works/dframework
 * @version     3.3.0
 */

namespace dFramework\core\support\traits;

/**
 *
 * @package     dFramework
 * @subpackage  Core
 * @category    Support/Traits
 * @author      Dimitri Sitchet Tomkeu <dev.dst@gmail.com>
 * @link        https://dimtrov.hebfree.org/docs/dframework/api
 * @since       3.3.0
 * @file        /system/core/support/traits/CliMessage.php
 */
trait CliMessage
{
    /**
     * @var array messages pour la console
     */
    private $messages = [];

    /**
     * Renvoi les messages pour la console
     *
     * @return array
     */
    public function getMessages() : array
    {
        return $this->messages;
    }
    /**
     * Rajoute un nouveau message a la pile de message
     *
     * @param string $message
     * @param string $color
     * @return self
     */
    private function pushMessage(string $message, string $color = 'green') : self
    {
        $this->messages[] = compact('message', 'color');
        return $this;
    }
}
