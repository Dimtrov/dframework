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
 * @link	    https://dimtrov.hebfree.org/works/dframework
 * @version 2.0
 */

use dFramework\core\exception\Exception;
use dFramework\dependencies\phpmailer\PHPMailer;
use dFramework\dependencies\phpmailer\SMTP;

/**
 * Mail
 *
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/Mail.html
 */


class dF_Mail
{
    protected $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer();

        $this->mail->CharSet = 'utf-8';
        $this->mail->isMail();
        $this->mail->SMTPAuth   = true;
        $this->mail->SMTPSecure =  PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port    = 587;
   }

   public function getMailer()
   {
       return $this->mail;
   }


    /**
     * @param array $data
     */
    public function connect(array $data)
    {
        if(empty($data['host']))
        {
            Exception::show('The parameter "host" is required');
        }
        $this->mail->Host = $data['host'];
        if(null !== $data['username'])
		{
			if(empty($data['username']) OR !is_string($data['username']))
			{
				Exception::show('The parameter "username" is required and it was to be a string');
			}
			$this->mail->Username = $data['username'];
			
		}
		if(null !== $data['password'])
		{
			if(empty($data['password']) OR !is_string($data['password']))
			{
				Exception::show('The parameter "password" is required and it was to be a string');
			}
			$this->mail->Password = $data['password'];			
		}
		if(!empty($data['port']) AND !is_int($data['port']))
        {
            $this->mail->Port = $data['port'];
        }
        if(isset($data['debug']) AND true === $data['debug'])
        {
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
    }

    public function set(array $data)
    {
        if(!empty($data['method']))
        {
            switch (strtolower($data['method']))
            {
                case 'mail':        $this->mail->isMail();
                    break;
                case 'qmail':       $this->mail->isQmail();
                    break;
                case 'sendmail':    $this->mail->isSendmail();
                    break;
                default:            $this->mail->isSMTP();
                    break;
            }
        }
        if(isset($data['timeout']) AND is_int($data['timeout']))
        {
            $this->mail->Timeout = $data['timeout'];
        }
        if(isset($data['charset']) AND is_string($data['charset']))
        {
            $this->mail->CharSet = $data['charset'];
        }
        if(isset($data['priority']) AND in_array((int)$data['priority'], [1, 3, 5]))
        {
            $this->mail->Priority = $data['priority'];
        }
        if(isset($data['encryption']))
        {
			if(null === $data['encryption'])
			{
				$this->mail->SMTPSecure = null;
			}
			if(strtolower($data['encryption']) == 'tls')
			{
				$this->mail->SMTPSecure =  PHPMailer::ENCRYPTION_STARTTLS;
			}
			if(strtolower($data['encryption']) == 'ssl')
			{
				$this->mail->SMTPSecure =  PHPMailer::ENCRYPTION_SMTPS;
			}
        }
    }

    public function use(string $name)
    {
        $name = strtolower($name);
        $config = (array) \dFramework\core\Config::get('email.'.$name);

        if(empty($config['connect']) OR !is_array($config['connect']))
        {
            Exception::show('Can\'t load a email connect parameters. check manuel to correct it');
        }
        $this->connect($config['connect']);

        if(!empty($config['set']) AND is_array($config['set']))
        {
            $this->set($config['set']);
        }
    }


    /**
     * @param string $address
     * @param null|string $name
     * @return dF_Mail
     * @throws \dFramework\dependencies\phpmailer\Exception
     */
    public function from(string $address, ?string $name = null) : self
    {
        $this->mail->setFrom($address, $name);
        return $this;
    }

    /**
     * @param string|array $address
     * @param string $name
     * @return dF_Mail
     * @throws \dFramework\dependencies\phpmailer\Exception
     */
    public function to($address, ?string $name = '') : self
    {
        if(is_string($address))
        {
            $this->mail->addAddress($address, $name);
        }
        if(is_array($address))
        {
            foreach ($address As $key => $value)
            {
                if(is_string($key) AND is_string($value))
                {
                    //Exception::show('Le tableau des destinataire ne peut avoir que des adresses mail');
                    $this->mail->addAddress($key, $value);
                }
            }
        }
        return $this;
    }

    /**
     * @param string|array $address
     * @param string $name
     * @return dF_Mail
     * @throws \dFramework\dependencies\phpmailer\Exception
     */
    public function reply($address, ?string $name = '') : self
    {
        if(is_string($address))
        {
            $this->mail->addReplyTo($address, $name);
        }
        if(is_array($address))
        {
            foreach ($address As $key => $value)
            {
                if(is_string($key) AND is_string($value))
                {
                    $this->mail->addReplyTo($key, $value);
                }
            }
        }
        return $this;
    }

    /**
     * @param string|array $address
     * @param string $name
     * @return dF_Mail
     * @throws \dFramework\dependencies\phpmailer\Exception
     */
    public function cc($address, ?string $name = '') : self
    {
        if(is_string($address))
        {
            $this->mail->addCC($address, $name);
        }
        if(is_array($address))
        {
            foreach ($address As $key => $value)
            {
                if(is_string($key) AND is_string($value))
                {
                    $this->mail->addCC($key, $value);
                }
            }
        }
        return $this;
    }

    /**
     * @param string|array $address
     * @param string $name
     * @return dF_Mail
     * @throws \dFramework\dependencies\phpmailer\Exception
     */
    public function bcc($address, ?string $name = '') : self
    {
        if(is_string($address))
        {
            $this->mail->addBCC($address, $name);
        }
        if(is_array($address))
        {
            foreach ($address As $key => $value)
            {
                if(is_string($key) AND is_string($value))
                {
                    $this->mail->addBCC($key, $value);
                }
            }
        }
        return $this;
    }

    /**
     * @param string|array $path
     * @param string $name
     * @return dF_Mail
     * @throws \dFramework\dependencies\phpmailer\Exception
     */
    public function attach($path, ?string $name = '') : self
    {
        if(is_string($path))
        {
            $this->mail->addAttachment($path, $name);
        }
        if(is_array($path))
        {
            foreach ($path As $key => $value)
            {
                if(is_string($key) AND is_string($value))
                {
                   // Exception::show('Le tableau des fichiers à joindre ne peut être qu\'une châine de caractère');
                    $this->mail->addAttachment($key, $value);
                }
            }
        }
        return $this;
    }

    /**
     * @param string|array $name
     * @param null $value
     * @return dF_Mail
     */
    public function header($name, $value = null) : self
    {
        if(is_string($name))
        {
            $this->mail->addCustomHeader($name, $value);
        }
        if(is_array($name))
        {
            foreach ($name As $key => $value)
            {
                if(is_string($key) AND (is_string($value) OR is_null($value)))
                {
                    $this->mail->addCustomHeader($key, $value);
                }
            }
        }
        return $this;
    }


    /**
     * @param string $subject
     * @return dF_Mail
     */
    public function subject(string $subject) : self
    {
        $this->mail->Subject = $subject;
        return $this;
    }

    /**
     * @param string $message
     * @return dF_Mail
     */
    public function message(string $message) : self
    {
        $this->mail->Body = $message;
        return $this;
    }

    /**
     * @param string $alt_body
     * @return dF_Mail
     */
    public function altBody(string $alt_body) : self
    {
        $this->mail->AltBody = $alt_body;
        return $this;
    }

    /**
     * @param bool $is_html
     * @return bool
     * @throws \dFramework\dependencies\phpmailer\Exception
     */
    public function send($is_html = true)
    {
        $this->mail->isHTML($is_html);
        return $this->mail->send();
    }

    /**
     * @return string
     */
    public function lastId()
    {
        return $this->mail->getLastMessageID();
    }
}