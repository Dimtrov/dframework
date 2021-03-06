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
 * @version     3.3.0
 */

namespace dFramework\libraries;

use dFramework\core\exception\Exception;
use dFramework\core\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Mail
 *
 *
 * @package		dFramework
 * @subpackage	Library
 * @author		Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 * @link		https://dimtrov.hebfree.org/works/dframework/docs/Mail.html
 * @since       2.0
 */
class Mail
{
	/**
	 * @var PHPMailer
	 */
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

   /**
	* Recupere l'instance de PHPMailer
	*
	* @return PHPMailer
    */
   public function getMailer() : PHPMailer
   {
       return $this->mail;
   }


    /**
     * Define a parameters for connexion in mail server
     *
     * @param array $params
     */
    public function connect(array $params)
    {
        if (empty($params['host']))
        {
            Exception::show('The parameter "host" is required');
        }
        $this->mail->Host = $params['host'];
        if (null !== $params['username'])
		{
			if (empty($params['username']) OR !is_string($params['username']))
			{
				Exception::show('The parameter "username" is required and it was to be a string');
			}
			$this->mail->Username = $params['username'];
		}
		if (null !== $params['password'])
		{
			if (empty($params['password']) OR !is_string($params['password']))
			{
				Exception::show('The parameter "password" is required and it was to be a string');
			}
			$this->mail->Password = $params['password'];
		}
		if (!empty($params['port']) AND !is_int($params['port']))
        {
            $this->mail->Port = $params['port'];
        }
        if (isset($params['debug']) AND true === $params['debug'])
        {
            $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
    }

    /**
     * Define a parameters for seding mail process
     *
     * @param array $params
     */
    public function set(array $params)
    {
        if (!empty($params['method']))
        {
            switch (strtolower($params['method']))
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
        if (isset($params['timeout']) AND is_int($params['timeout']))
        {
            $this->mail->Timeout = $params['timeout'];
        }
        if (isset($params['charset']) AND is_string($params['charset']))
        {
            $this->mail->CharSet = $params['charset'];
        }
        if (isset($params['priority']) AND in_array((int)$params['priority'], [1, 3, 5]))
        {
            $this->mail->Priority = $params['priority'];
        }
        if (isset($params['encryption']))
        {
			if (null === $params['encryption'])
			{
				$this->mail->SMTPSecure = null;
			}
			if (strtolower($params['encryption']) == 'tls')
			{
				$this->mail->SMTPSecure =  PHPMailer::ENCRYPTION_STARTTLS;
			}
			if (strtolower($params['encryption']) == 'ssl')
			{
				$this->mail->SMTPSecure =  PHPMailer::ENCRYPTION_SMTPS;
			}
        }
    }

    /**
     * Tell a configuration that use for email process
     *
     * @param string $name
     */
    public function use(string $name)
    {
        $name = strtolower($name);
        $config = (array) Config::get('email.'.$name);

        if (empty($config['connect']) OR !is_array($config['connect']))
        {
            Exception::show('Can\'t load a email connect parameters. check manuel to correct it');
        }
        $this->connect($config['connect']);

        if (!empty($config['set']) AND is_array($config['set']))
        {
            $this->set($config['set']);
        }
    }


    /**
	 * Defini l'adresse de l'expéditeur (From) du mail
	 *
     * @param string $address
     * @param null|string $name
     * @return self
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function from(string $address, ?string $name = null) : self
    {
        $this->mail->setFrom($address, $name);
        return $this;
    }

    /**
	 * Ajoute l'adresse de destination (To) du mail
	 *
     * @param string|array $address
     * @param string $name
     * @return self
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function to($address, ?string $name = '') : self
    {
        if (is_string($address))
        {
            $this->mail->addAddress($address, $name);
        }
        if (is_array($address))
        {
            foreach ($address As $key => $value)
            {
                if (is_string($key) AND is_string($value))
                {
                    $this->mail->addAddress($key, $value);
                }
            }
        }
        return $this;
    }

    /**
	 * Ajoute les adresses de reponse (Reply-To) au mail
	 *
     * @param string|array $address
     * @param string $name
	 * @return self
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function reply($address, ?string $name = '') : self
    {
        if (is_string($address))
        {
            $this->mail->addReplyTo($address, $name);
        }
        if (is_array($address))
        {
            foreach ($address As $key => $value)
            {
                if (is_string($key) AND is_string($value))
                {
                    $this->mail->addReplyTo($key, $value);
                }
            }
        }
        return $this;
    }

    /**
	 * Ajoute des adresses de copie (CC) au mail
	 *
     * @param string|array $address
     * @param string $name
     * @return self
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function cc($address, ?string $name = '') : self
    {
        if (is_string($address))
        {
            $this->mail->addCC($address, $name);
        }
        if (is_array($address))
        {
            foreach ($address As $key => $value)
            {
                if (is_string($key) AND is_string($value))
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
     * @return self
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function bcc($address, ?string $name = '') : self
    {
        if (is_string($address))
        {
            $this->mail->addBCC($address, $name);
        }
        if (is_array($address))
        {
            foreach ($address As $key => $value)
            {
                if (is_string($key) AND is_string($value))
                {
                    $this->mail->addBCC($key, $value);
                }
            }
        }
        return $this;
    }

    /**
	 * Ajoute des pièces jointes au mail
	 *
     * @param string|array $path
     * @param string $name
     * @return self
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function attach($path, ?string $name = '') : self
    {
        if (is_string($path))
        {
            $this->mail->addAttachment($path, $name);
        }
        if (is_array($path))
        {
            foreach ($path As $key => $value)
            {
                if (is_string($key) AND is_string($value))
                {
                   // Exception::show('Le tableau des fichiers à joindre ne peut être qu\'une châine de caractère');
                    $this->mail->addAttachment($key, $value);
                }
            }
        }
        return $this;
    }

    /**
	 * Ajoute des entêtes personnalisées au mail à envoyer
	 *
     * @param string|array $name
     * @return self
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function header($name, $value = null) : self
    {
        if (is_string($name))
        {
            $this->mail->addCustomHeader($name, $value);
        }
        if (is_array($name))
        {
            foreach ($name As $key => $value)
            {
                if (is_string($key) AND (is_string($value) OR is_null($value)))
                {
                    $this->mail->addCustomHeader($key, $value);
                }
            }
        }
        return $this;
    }


    /**
	 * Defini le sujet du mail
	 *
     * @param string $subject
     * @return self
     */
    public function subject(string $subject) : self
    {
        $this->mail->Subject = $subject;
        return $this;
    }

    /**
	 * Defini le message à envoyer
	 *
     * @param string $message
     * @return self
     */
    public function message(string $message) : self
    {
        $this->mail->Body = $message;
        return $this;
    }

    /**
	 * Ajoute un texte alternatif pour le message en cas de nom prise en charge du html
	 *
     * @param string $alt_body
     * @return self
     */
    public function altBody(string $alt_body) : self
    {
        $this->mail->AltBody = $alt_body;
        return $this;
    }

    /**
	 * Lance l'envoi du mail
	 *
     * @param bool $is_html
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send(bool $is_html = true) : bool
    {
        $this->mail->isHTML($is_html);

        return $this->mail->send();
    }

    /**
	 * Renvoie l'identifiant du dernier mail envoyé
	 *
     * @return string
     */
    public function lastId() : string
    {
        return $this->mail->getLastMessageID();
    }
}
