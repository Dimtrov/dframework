<?php
namespace dFramework\dependencies\others\filipegomes;

//======================================================================================================================
//
// CLASS UPLOAD DE FICHIER POUR PHP5
//
//======================================================================================================================
// Nom du fichier      : class.upload.php
// Date de création    : 24/05/2010
// Date de mise à jour : Y en a pas (première programmation)
// Programmé par       : Filipe Gomes
// Email               : filipegomes@live.fr
// Site web            : http://www.dev-goneti.fr
//
// LICENCE:
// Bah, rien c'est gratos mais n'hésitez pas à mettre un petit lien ou vous voulez vers mon site (www.dev-goneti.fr),
// se serait sympas merci!!! Par contre si vous le mettez en téléchargement sur votre site ou ailleurs merci de laisser
// tous les commentaires même se qu'il y a au-dessus, encore merci.
//
// ATTENTION,
// IL Y A BEAUCOUP DE COMMENTAIRES, JE SAIS C'EST CHIANT MAIS C'EST POUR VOTRE BIEN ET POUR MIEUX COMPRENDRE
// SON UTILISATION ET SON FONCTIONNEMENT.
//
//======================================================================================================================
// A VERIFIER IMPERATIVEMENT DANS LE phpinfo(); DE VOTRE SERVEUR
//======================================================================================================================
//  - upload_max_filesize : fixe la taille maximum des fichiers chargés par les méthodes POST et PUT
//  - post_max_size : (supérieur à upload_max_filesize) fixe la taille maximum des fichiers chargés par la méthode POST
//  - memory_limit  : (supérieur à post_max_size) fixe la taille de la mémoire du script
// 	- Vous devez être en PHP5
//
//======================================================================================================================
// EXEMPLE D'UTILISATION
//======================================================================================================================
// Dans le fichier PHP contenant votre formulaire:
//
// // on inclu la class
// require("class.upload.php");
// // on déclare la class
// $obj = new upload('mon_repertoire/', 'nom_du_fichier_post');
// // on déclare les variables qu'on a besoin
// $obj->cl_taille_maxi = 49000000; // pour 49 Mo maximum
// $obj->cl_extensions = array('.gif','.jpg','.png'); // les extensions autorisées
// // on envoi le fichier
// if (!$obj->uploadFichier())
// {
// 		// affichage d'une erreur en cas d'echec
//		echo $obj->affichageErreur();
// }
// else
// {
//	 	// autrement tout est ok!
// }
//
// Cette class donne d'autre possibilités comme optenir le nom du fichier avec cGetNameFile(); ou optenir
// l'extension du fichier avec cGetExtension(); etc... C'est une class très complète et vous sera toujours utile.
// BON COURAGE !!!
//
//======================================================================================================================
class Upload
{
    private $cl_file = "";
    private $cl_fichier = "";
    private $cl_taille = 0;
    private $cl_extension_fichier = "";
    private $cl_dossier = "./";
    private $cl_erreur = "";
    private $cl_new_name = "";
    public $cl_taille_maxi = 0;
    public $cl_extensions = array();
    public $cl_nb_char_aleatoire = 12;


    //*************************************************************************************
    //*
    //* LES MESSAGES D'ERREUR (avec les explications lol)
    //*
    //*************************************************************************************
    //* Utile si votre site dispose de plusieurs languages ou si vous voulez mettre les
    //* textes à votre façon
    //*************************************************************************************
    // message d'erreur si le fichier téléchargé excède la taille de upload_max_filesize, configurée dans le php.ini
    public $cl_message_01 = "Le fichier t&eacute;l&eacute;charg&eacute; exc&egrave;de la taille de upload_max_filesize, configur&eacute;e dans le php.ini.";
    // message d'erreur si le fichier téléchargé excède la taille de MAX_FILE_SIZE, qui a été spécifiée dans le formulaire HTML
    public $cl_message_02 = "Le fichier d&eacute;passe la limite autoris&eacute;e dans le formulaire HTML.";
    // message d'erreur si le fichier n'a été que partiellement téléchargé
    public $cl_message_03 = "L'envoi du fichier a &eacute;t&eacute; interrompu pendant le transfert.";
    // message d'erreur si aucun fichier n'a été téléchargé
    public $cl_message_04 = "Aucun fichier n'a &eacute;t&eacute; t&eacute;l&eacute;charg&eacute;.";
    // message d'erreur si le dossier temporaire est manquant.
    public $cl_message_05 = "Impossible de trouver le dossier temporaire.";
    // message d'erreur si il y a eu échec d'écriture du fichier dans le serveur
    public $cl_message_06 = "Echec de l'&eacute;criture du fichier sur le serveur.";
    // message d'erreur si l'envoi de fichier est arrêté par l'extension
    public $cl_message_07 = "Erreur dans l'extension de votre fichier.";
    // message d'erreur si il y a eu une erreur inconnue !!!
    public $cl_message_08 = "Erreur inconnue durant l'upload !!!";
    // message d'erreur si l'extension du fichier n'est pas valide
    public $cl_message_09 = "Vous devez choisir un fichier de type : ";
    public $cl_message_10 = " ou ";
    // message d'erreur si le fichier est trop gros
    public $cl_message_11 = "Le fichier est trop gros, impossible de l'envoyer.";
    // message d'erreur si le fichier existe déjà dans le répertoire de destination
    public $cl_message_12 = "Le nom du fichier existe d&eacute;j&agrave;.";
    // message d'erreur si il y a eu une erreur inconnue !!!
    public $cl_message_14 = "Erreur inconnue lors du chargement du fichier !"; // désolé je n'aime pas le chiffre 13 ;)
    // message d'erreur si le répertoire n'existe pas
    public $cl_message_15 = "Le r&eacute;pertoire ou doit &ecirc;tre envoy&eacute; le fichier n'existe pas.";


    //*********************************************************************
    //*
    //* CONSTRUCTEUR
    //* Création de l'instance de la classe
    //*
    //*********************************************************************
    //* $repUpload (facultatif) :
    //* 	nom du répertoire ou doit être chargé le fichier
    //*		exemple : '../mon_repertoire/' ou 'mon_repertoire/'
    //*
    //* $clfichier (obligatoire) :
    //* 	nom de la balise INPUT FILE de votre formulaire
    //*		exemple : <input type="file" name="mon_fichier" />
    //*		vous mettez alors 'mon_fichier'
    //*
    //* Quelques exemple d'appel du constructeur dans votre fichier PHP:
    //* 	$objUpload = new upload('../mon_repertoire/', 'mon_fichier');
    //* 	ou
    //* 	$objUpload = new upload('mon_repertoire/', 'mon_fichier');
    //* 	ou
    //* 	$objUpload = new upload('mon_fichier');
    //*
    //*********************************************************************
    public function __construct($repUpload="./", $clfichier)
    {
        $this->cl_file = $clfichier;
        $this->cl_fichier = basename($this->cl_file['name']);
        $extFichier = strrchr($this->cl_file['name'], '.');
        $this->cl_extension_fichier = strtolower($extFichier);
        // on récupère le répertoire de destination
        $this->cl_dossier = $repUpload;
    }


    //*********************************************************************
    //*
    //* ENVOI LE FICHIER VERS LE SERVEUR
    //*
    //* $nouveau_nom_fichier (facultatif) :
    //*		si vous voulez mettre un nom différent du nom de fichier que
    //*		vous voulez envoyez, mettez celui de votre choix, sinon
    //*		ne mettez rien.
    //*		exemple : 'nouveau_nom'
    //*		surtout ne pas mettrez l'extension à la fin, le programme
    //*		se charge de le faire automatiquement.
    //*     pour mettre un nom aléatoire mettez 'aleatoire'
    //*
    //* exemple :
    //* uploadFichier(); // le nom du fichier à envoyer sera le nom final
    //* uploadFichier('mettez_ce_que_vous_voulez_sauf_aleatoire'); // nom de fichier final personnalisé (ne pas mettre 'aleatoire')
    //* uploadFichier('aleatoire'); // choisir un nom de fichier aléatoire
    //*
    //* vous pouvez definir le nombre de caractères aléatoire en utilisant $cl_nb_char_aleatoire avant cette fonction
    //* voir les exemples contenu dans le zip que vous avez téléchargé
    //*
    //*********************************************************************
    public function uploadFichier($nouveau_nom_fichier="")
    {
        //-------------------------------------------------------------------
        // on vérifie s'il faut donner un nom spécifique au fichier final
        //-------------------------------------------------------------------
        // tout d'abord on vérifie que le répertoire existe bien pour éviter tout erreur
        if (is_dir($this->cl_dossier))
        {
            // le nom final du fichier doit être aléatoire
            if (!empty($nouveau_nom_fichier) && $nouveau_nom_fichier=='aleatoire')
            {
                // on liste le nombre de fichiers disponibles dans le répertoire demandé
                // cette procédure ajoutera le nombre de fichier dans le nom aléatoire
                // final et évitera les doublons
                $nbFile = 0;
                $doss = opendir($this->cl_dossier);
                while ($w = readdir($doss)) { if (!is_dir($w)) $nbFile++; }
                closedir($doss);

                // on attribut un nom aléatoire
                $strf = ""; $str = "abcdefghijklmnpqrstuvwxy0123456789_";
                srand((double)microtime()*1000000);
                for($i=0; $i<$this->cl_nb_char_aleatoire; $i++)
                {
                    $strf .= $str[rand()%strlen($str)];
                }
                // on récupère le nom aléatoire en ajoutant à la fin le nombre de fichiers
                // grace à $nbFile sans oublier l'extension à la fin
                $this->cl_new_name = $strf.$nbFile.$this->cl_extension_fichier;
            }
            // le nom du fichier final est saisi par l'utilisateur
            else if (!empty($nouveau_nom_fichier) && $nouveau_nom_fichier!='aleatoire')
            {
                $this->cl_new_name = $nouveau_nom_fichier.$this->cl_extension_fichier;
            }
            // autrement on récupère le nom d'origine du fichier à uploader
            else
            {
                $this->cl_new_name = $this->cl_fichier;
            }
        }
        else
        {
            // aïe, problème, le répertoire n'existe pas, on retourne tout ça faux
            $this->cl_erreur = $this->cl_message_15;
            return false;
        }


        // vérification des erreurs de chargement de fichiers
        if ($this->cl_file['error'])
        {
            switch ($this->cl_file['error'])
            {
                // UPLOAD_ERR_INI_SIZE : Le fichier téléchargé excède la taille de upload_max_filesize, configurée dans le php.ini.
                case 1:
                    $this->cl_erreur = $this->cl_message_01;
                    return false;
                    break;

                // UPLOAD_ERR_FORM_SIZE : Le fichier téléchargé excède la taille de MAX_FILE_SIZE, qui a été spécifiée dans le formulaire HTML.
                case 2:
                    $this->cl_erreur = $this->cl_message_02;
                    return false;
                    break;

                // UPLOAD_ERR_PARTIAL : Le fichier n'a été que partiellement téléchargé.
                case 3:
                    $this->cl_erreur = $this->cl_message_03;
                    return false;
                    break;

                // UPLOAD_ERR_NO_FILE : Aucun fichier n'a été téléchargé.
                case 4:
                    $this->cl_erreur = $this->cl_message_04;
                    return false;
                    break;

                // UPLOAD_ERR_NO_TMP_DIR : Un dossier temporaire est manquant. Introduit en PHP 4.3.10 et PHP 5.0.3.
                case 6:
                    $this->cl_erreur = $this->cl_message_05;
                    return false;
                    break;

                // UPLOAD_ERR_CANT_WRITE : Echec de l'écriture du fichier sur le disque. Introduit en PHP 5.1.0.
                case 7:
                    $this->cl_erreur = $this->cl_message_06;
                    return false;
                    break;

                // UPLOAD_ERR_EXTENSION : L'envoi de fichier est arrêté par l'extension. Introduit en PHP 5.2.0.
                case 8:
                    $this->cl_erreur = $this->cl_message_07;
                    return false;
                    break;

                // Au cas ou...
                default:
                    $this->cl_erreur = $this->cl_message_08;
                    return false;
                    break;
            }
        }
        // l'extension choisie n'est pas valide
        else if (!in_array($this->cl_extension_fichier, $this->cl_extensions))
        {
            $this->cl_erreur .= $this->cl_message_09;
            for($i=0; $i<count($this->cl_extensions); $i++)
            {
                if ($i==0) $this->cl_erreur .= $this->cl_extensions[$i];
                if (count($this->cl_extensions)>1 && $i>0 && $i<count($this->cl_extensions)-1) $this->cl_erreur .= ', '.$this->cl_extensions[$i];
                if (count($this->cl_extensions)>1 && $i>=count($this->cl_extensions)-1) $this->cl_erreur .= $this->cl_message_10.$this->cl_extensions[$i];
            }
            return false;
        }
        // la taille du fichier est supérieure à la taille maximum
        else if ($this->cl_file['size'] >= $this->cl_taille_maxi)
        {
            $this->cl_erreur = $this->cl_message_11;
            return false;
        }
        // vérifie si le fichier existe déjà dans le répertoire
        else if (file_exists($this->cl_dossier.$this->cl_new_name))
        {
            $this->cl_erreur = $this->cl_message_12;
            return false;
        }
        // upload du fichier, retourne TRUE si l'upload c'est bien déroulé
        else if(move_uploaded_file($this->cl_file['tmp_name'], $this->cl_dossier.$this->cl_new_name))
        {
            return true;
        }
        // au pire on renvoi FALSE
        else
        {
            $this->cl_erreur = $this->cl_message_14;
            return false;
        }
    }


    //*********************************************************************
    //*
    //* Retourne le message d'erreur en cas d'echec de l'upload
    //*
    //*********************************************************************
    public function affichageErreur()
    {
        return $this->cl_erreur;
    }





    //*********************************************************************
    //*
    //* Retourne l'extension du fichier
    //*
    //*********************************************************************
    public function cGetExtension()
    {
        return 	$this->cl_extension_fichier;
    }

    //*********************************************************************
    //*
    //* Retourne le nom du fichier avec ou sans l'extension
    //*
    //* $ext est une variable facultative elle renvoie par défaut true et
    //* affichera le nom du fichier avec l'extension
    //*
    //* Utilisation:
    //* cGetNameFile(true)  : renvoi le nom du fichier avec l'extension
    //* cGetNameFile(false) : renvoi le nom du fichier sans extension
    //* cGetNameFile()      : renvoi le nom du fichier avec l'extension
    //*********************************************************************
    public function cGetNameFile($ext=true)
    {
        if ($ext)
            return 	$this->cl_fichier;
        else
            return basename(strtolower($this->cl_fichier), $this->cl_extension_fichier);
    }

    //*********************************************************************
    //*
    //* Retourne le nom du fichier final avec ou sans l'extension
    //*
    //* $ext est une variable facultative elle renvoie par défaut true et
    //* affichera le nom du fichier avec l'extension
    //*
    //* Utilisation:
    //* cGetNameFileFinal(true)  : renvoi le nom du fichier avec l'extension
    //* cGetNameFileFinal(false) : renvoi le nom du fichier sans extension
    //* cGetNameFileFinal()      : renvoi le nom du fichier avec l'extension
    //*********************************************************************
    public function cGetNameFileFinal($ext=true)
    {
        if ($ext)
            return 	$this->cl_new_name;
        else
            return basename($this->cl_new_name, $this->cl_extension_fichier);
    }

    //*********************************************************************
    //*
    //* Retourne le répertoire de destination
    //*
    //*********************************************************************
    public function cGetFolder()
    {
        return 	$this->cl_dossier;
    }

    //*********************************************************************
    //*
    //* Retourne la taille du fichier selon votre choix
    //*
    //* cGetSizeFile(1); // Retourne la taille en octet (valeur par défaut)
    //* cGetSizeFile(2); // Retourne la taille en kilo-octet (ko)
    //* cGetSizeFile(3); // Retourne la taille en méga-octet (mo)
    //* cGetSizeFile(4); // Retourne la taille en giga-octet (go)
    //* cGetSizeFile(5); // Retourne la taille en téra-octet (to)
    //*
    //* $ponc vous permet de mettre soit une vigule soit un point ou
    //* autre chose de votre choix pour séparer les chiffres (facultatif)
    //*********************************************************************
    public function cGetSizeFile($type=0, $ponc=",")
    {
        // déclaration des variables
        $valeur = $this->cl_file['size'];
        $retour = "";

        switch ($type)
        {
            // retourne la taille du fichier en Octet
            case 1: $retour = $valeur; break;
            // retourne la taille du fichier en Ko
            case 2: $retour = round($valeur / 1024 * 100) / 100; break;
            // retourne la taille du fichier en Mo
            case 3: $retour = round($valeur / 1048576 * 100) / 100; break;
            // retourne la taille du fichier en Go
            case 4: $retour = round($valeur / 1073741824 * 100) / 100; break;
            // retourne la taille du fichier en To
            case 5: $retour = round($valeur / 1099511627776 * 100) / 100; break;
            // par défaut en retourne la valeur en octet
            default: $retour = $valeur; break;
        }
        // on renvoi le résultat
        return str_replace('.', $ponc, $retour);
    }

    //*********************************************************************
    //*
    //* Retourne le nom temporaire du fichier
    //*
    //*********************************************************************
    public function cGetNameTemp()
    {
        return $this->cl_file['tmp_name'];
    }

    //*********************************************************************
    //*
    //* Retourne le type de fichier
    //*
    //*********************************************************************
    public function cGetTypeFile()
    {
        return $this->cl_file['type'];
    }
}