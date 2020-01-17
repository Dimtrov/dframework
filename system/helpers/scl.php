<?php
/***
 *----------- ShortCodeLib-php V3 --------------
 *
 *
 *    Bienenue à la bibliothèque de fonctions php "ShortCodeLib 3"
 *
 *    Cette bibliothèque a été mise sur pied pour faciliter le développement des projets php
 *
 *    ---- @Auteur : Sitchet Tomkeu Dimitric - Elève Ingénieur des travaux informatique option Génie Loiciel
 *    ---- @Contact : (+237) 691 88 95 87 - 673 40 66 61 / dev.dimitrisitchet@gmail.com
 *    ---- @Licence : Creative Commons 4.0
 *            Vous êtes libre d'utiliser, de modifier et de partager cette bibliothèque à
 *            condition expresse de respecter toutes les conditions ci-dessous
 *                1 - CC-BY : En utilisant, en modifiant, ou en partageant ce code, vous
 *                    reconnaissez qu'il a été crée par Sitchet Tomkeu Dimitric
 *                2 - CC-SA : Pour partager ce code, vous devez le faire suivant les mêmes
 *                    conditions d'utilisation sus énumerées. Le nom de son auteur doit
 *                    apparaitre dans toutes les copies.
 *
 *    ---- Nouveautés V3.0 :
 *            Par Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com> le 09/05/2019
 *            1) Ajout de la fonction scl_cypher() qui crypte et decrypte une chaine par simple substitution (ancienne fonctionnalite de scl_hash)
 *            2) Modification de la fonction scl_hash() en fonction de hashage a sens unique
 *
 *    ---- Nouveautés V3.0.1 :
 *            Par Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com> le 10/05/2019
 *            1) Modification de la fonction scl_include() pour gerer les inclusions faibles et fortes
 *
 *    ---- Nouveautés V3.2 :
 *            Par Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com> le 13/08/2019
 *            1) Ajout de la fonction scl_byte2size() qui renvoie le nombre de kb, mo, gb en fonction du nombre de byte passé en parametre
 *            2) Ajout de la fonction scl_int2letter() qui transforme un chiffre en lettre
 *
 *    ---- Nouveautés V3.2.1 :
 *            Par Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com> le 15/08/2019
 *            1) Modification de la fonction scl_debug() pour adopter une mise en page plus moderne
 *
 *    ---- Nouveautés V3.3 :
 *            Par Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com> le 12/01/2020
 *            1) Ajout de la fonction scl_shortenStr() qui tronque une chaine en ajoutant les mots de fin
 ***/


/**
 * ------- FUNCTION SCL_UPLOAD()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *
 * @brief Cette fonction permet de récupérer un fichier envoyer par formulaire
 * @return mixed renvoie true si tous s'est bien passé et en cas d'erreur, renvoie un tableau contenant le code de l'erreur et le message par defaut
 * @param array $input la super globale $_FILE['nom'] qui permet de récupérer toutes les informations concernant le fichier.
 * @param string $path le dossier du serveur où l'on souhaite ranger le fichier
 * @param int [$size]  la taille maximale en Ko du fichier pouvant être téléchargé
 * @param string [$type] représente le type de fichier pouvant être téléchargé
 * Cette fonction prend en charge 6 types de fichier (image, video, audio, doc, zip, et web)
 * mais le type par défaut est 'all' pour spécifier que les 6 types ci-dessus (24 extensions) sont pris en compte
 * @param string [$output]  le nom avec lequel le fichier sera enregistré.
 */
function scl_upLoad($input, $path = '', $size = 2500000, $type = '', $output = '')
{
    // taille par défaut
    $size = (empty($size) OR !is_numeric($size)) ? 2500000 : $size;
    // Le dossier par defaut est la racine
    $path = (!empty($path) AND !file_exists($path)) ? '' : htmlspecialchars($path);


    if (!is_array($input)) {
        return array(0, 'Entrée non valide');
    }
    if (empty($input['tmp_name']) OR empty($input['name']) OR empty($input['size'])) {
        return array(1, 'Informations d\'entrées incomplètes');
    }
    if ($input['size'] > $size) {
        return array(2, 'Fichier trop volumineux');
    }


    /* vérification du type et définition des extensions autorisées */
    if ($type == 'image') {
        $extensions = array('jpg', 'jpeg', 'png', 'gif');
    } else if ($type == 'audio') {
        $extensions = array('mp3', 'wav', 'mpeg');
    } else if ($type == 'video') {
        $extensions = array('mp4', 'avi', 'ogv', 'webm', 'flv', 'mov');
    } else if ($type == 'doc') {
        $extensions = array('txt', 'doc', 'docx', 'pdf');
    } else if ($type == 'web') {
        $extensions = array('htm', 'html', 'css', 'xml', 'js', 'json');
    } else if ($type == 'db') {
        $extensions = array('sql', 'vcf');
    } else if ($type == 'zip') {
        $extensions = array('zip', 'rar');
    } else {
        // Si on veut travailler avec une(plusieurs) extensions spécifique du même type
        if (preg_match('#^(image|audio|video|doc|web|db|zip){1}/(.*?)$#i', $type)) {
            $extensions = explode('/', $type)[1];
            if (empty($extensions)) {
                // Si il n y'a rien après le slash
                switch (explode('/', $type)[0]) {
                    case 'image' :
                        $extensions = array('jpg', 'jpeg', 'png', 'gif');
                        break;
                    case 'audio' :
                        $extensions = array('mp3', 'wav', 'mpeg');
                        break;
                    case 'video' :
                        $extensions = array('mp4', 'avi', 'ogv', 'webm', 'flv', 'mov');
                        break;
                    case 'doc' :
                        $extensions = array('txt', 'doc', 'docx', 'pdf');
                        break;
                    case 'web' :
                        $extensions = array('htm', 'html', 'css', 'xml', 'js', 'json');
                        break;
                    case 'db' :
                        $extensions = array('sql', 'vcf');
                        break;
                    case 'zip' :
                        $extensions = array('zip', 'rar');
                        break;
                }
            } else if (count(explode(',', $extensions)) < 2) {
                $extensions = array(explode(',', $extensions)[0]); // Si il y'a une seule extension
            } else {
                $ext = explode(',', $extensions);
                $extensions = array(); // Si il y'a plusieurs extensions
                foreach ($ext AS $ex) {
                    $extensions[] = $ex;
                }
            }
        } else {
            $extensions = array(
                'jpg', 'jpeg', 'png', 'gif', 'mp3', 'wav', 'mpeg', 'mp4', 'avi', 'ogv', 'webm', 'flv', 'mov', 'txt', 'doc', 'docx',
                'pdf', 'htm', 'html', 'css', 'xml', 'js', 'json', 'sql', 'vcf', 'zip', 'rar'
            );
        }
    }

    if (!in_array(strtolower(pathinfo($input['name'])['extension']), $extensions)) {
        return array(3, 'Extension non prise en charge');
    }


    /*
    vérification de la valeur du nom d'enregistrement ($output)
    --  $extension = '0.' signifie qu'on ne change pas l'extension du fichier tandis que
        $extension = '1.' signifie qu'on change. donc nous sommes dans le cas où le '.extension' a été spécifié
    --  NB: ne pas confondre $extensions et $extension. l'un représente les différents extension autorisés et
        l'autre vérifie si l'extension originale du fichier doit être modifié ou non
    */
    if (empty($output)) // Si on ne defini pas le nom de sortie...
    {
        // on enregistre le fichier avec la date et l'heure courante
        $output = 'scl_' . date('Ymd') . '-' . date('His');
        $extension = '0.';
    } else // Si on defini le nom de sortie...
    {
        if (!empty(explode('.', $output)[1]) AND in_array(strtolower(explode('.', $output)[1]), $extensions))  // Si l'extension est presente dans ce nom et est valide...
        {
            $out = explode('.', $output);
            $output = $out[0]; // On enregistre le fichier avec le nom specifié
            $extension = '1.' . $out[1]; // On enregistre le fichier avec l'extension specifié (changement d'extension)
        } else // Sinon...on enregistre le fichier avec le nom specifié mais en conservant son extension
        {
            $output = str_replace('.', '', $output);
            $output = str_replace(',', '', $output);
            $output = str_replace(';', '', $output);
            $extension = '0.';
        }
    }
    // si on a prévu modifier l'extension du fichier
    if (explode('.', $extension)[0] == 1) {
        $extension = explode('.', $extension)[1]; // l'extension est le celui specifié
    } else {
        $extension = strtolower(pathinfo($input['name'])['extension']); // 'extension est celui de départ
    }


    // si le fichier n'a pas été téléchargé
    if (!move_uploaded_file($input['tmp_name'], $path . $output . '.' . $extension)) {
        return array(4, 'Erreur de téléversement');
    }
    return true;
}


/**
 * ------- FUNCTION SCL_MINIMIZEIMG()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail>
 *
 * @brief Cette fonction permet diminuer les dimensions d'une image
 * @return mixed renvoie true si tous s'est bien passé et en cas d'erreur, renvoie un tableau contenant le code de l'erreur et le message par defaut
 *
 * @param string $src la source de l'image a redimentionner.
 * @param array|int [$size]  le tableau contenant les nouvelles dimensions ou la dimension unique(hauteur = largeur).
 * @param bool [$relative]  specifie si on redimensionne la photo proportionnellement aux dimensions initiales
 */
function scl_minimizeImg($src, $size = array(), $relative = false)
{
    // Si le fichier n'existe pas
    if (!file_exists($src)) {
        return array(0, 'Fichier inexistant');
    }
    // Si on n'envoi pas un tableau ou un nombre comme tailles
    if (empty($size) OR (!is_array($size) AND !is_int($size))) {
        return array(1, 'Mauvaises dimensions de redimensionnement');
    }

    // Si on envoie un tableau comme $size
    if (is_array($size)) {
        // Si le tableau n'a pas 1 ou deux element
        if (count($size) < 1 OR count($size) > 2) {
            return array(2, 'Violation du nombre de dimension');
        }
        // Si l'un des element du tableau n'est pas un nombre
        if (!is_int($size[0]) OR (!empty($size[1]) AND !is_int($size[1]))) {
            return array(3, 'Type de dimension inconnu');
        }
    }

    // Les nouvelles dimensions
    $dimensions = array('x' => 0, 'y' => 0);

    if (is_int($size)) {
        $dimensions['x'] = $size;
        $dimensions['y'] = $size;
    } else {
        if (count($size) == 1) {
            $dimensions['x'] = $size[0];
            $dimensions['y'] = $size[0];
        } else {
            $dimensions['x'] = $size[0];
            $dimensions['y'] = $size[1];
        }
    }

    if (preg_match('#\.jpe?g$#i', $src)) {
        $source = imagecreatefromjpeg($src);
    }
    if (preg_match('#\.png$#i', $src)) {
        $source = imagecreatefrompng($src);
    }
    if (preg_match('#\.gif$#i', $src)) {
        $source = imagecreatefromgif($src);
    }

    // Les dimensions de l'image source
    $l_s = imagesx($source); // Largeur
    $h_s = imagesy($source); // Hauteur

    // Les dimensions de la destination
    if ($relative) {
        $l_d = ($dimensions['x'] / 100) * $l_s;
        $h_d = ($dimensions['y'] / 100) * $h_s;
    } else {
        $l_d = ($dimensions['x'] <= 10) ? $l_s : $dimensions['x']; // Largeur
        $h_d = ($dimensions['y'] <= 10) ? $h_s : $dimensions['y']; // Hauteur
    }

    // On crée la miniature vide
    $destination = imagecreatetruecolor($l_d, $h_d);

    // On crée la miniature
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $l_d, $h_d, $l_s, $h_s);


    // On enregistre la miniature
    if (preg_match('#\.jpe?g$#i', $src)) {
        $result = imagejpeg($destination, $src);
    }
    if (preg_match('#\.png$#i', $src)) {
        $result = imagepng($destination, $src);
    }
    if (preg_match('#\.gif$#i', $src)) {
        $result = imagegif($destination, $src);
    }

    return ($result == true) ? true : array(5, 'Erreur lors du redimensionnement');
}


/**
 * ------- FUNCTION SCL_GENERATEKEYS()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *
 * @brief Cette fonction permet de une chaine aléatoirement
 * @return string
 *
 * @param int [$nbr]  le nombre de caractères de la clé.
 * @param int [$type]  le type de clé à generer.
 */
function scl_generateKeys($nbr = 8, $type = 0)
{
    /* Valeurs par défaut */
    $nbr = (empty($nbr)) ? 8 : (int)$nbr;
    $nbr = (!is_int($nbr)) ? 8 : (int)$nbr;
    $nbr = ($nbr < 3 OR $nbr > 64) ? 8 : (int)$nbr;

    switch ($type) {
        case 1 :
            $chars = range('a', 'z');
            break; // Caractères alphabetique minuscules
        case 2 :
            $chars = range('A', 'Z');
            break; // Caractères alphabetique majuscules
        case 3 :
            $chars = range(0, 9);
            break; // Caractères numerique
        case 4 :
            $chars = array_merge(range('a', 'z'), range('A', 'Z'));
            break; // Caractères alphabetique
        case 5 :
            $chars = array_merge(range(0, 9), range('a', 'z'));
            break; // Caractères numerique et alphabetique minuscules
        case 6 :
            $chars = array_merge(range(0, 9), range('A', 'Z'));
            break; // Caractères numerique et alphabetique majuscules
        default :
            $chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
            break; // Tous les caractères
    }
    $return = ''; // Valeur de retour
    $nb_char = count($chars); // On compte le nombre de caractères disponible

    for ($i = 0; $i < $nbr; $i++) {
        // On tire un nombre au hasard parmi toutes les indexes de caracteres et on affiche le caracteres corespondant
        $return .= $chars[rand(0, ($nb_char - 1))];
    }
    return $return;
}


/**
 * ------- FUNCTION SCL_DATE()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @brief Cette fonction permet de formater une date
 * @return string
 *
 * @param string $date la date à formater
 * @param string [$format]  le format de sortie
 * @param bool [$interval] specifie si l'interval va etre donné (true) ou pas (false)
 * @param string [$fuseau]  le fuseau horaire
 */
function scl_date($date, $format = 'D, d M Y', $interval = true, $fuseau = 'Europe/Paris')
{
    /* Valeurs par défaut */
    $format = (empty($format) OR !is_string($format)) ? 'D, d M Y' : htmlspecialchars(trim($format)); // Le format de sortie, par défaut = D, d M Y
    $fuseau = (empty($fuseau) OR !is_string($fuseau)) ? 'Europe/Paris' : htmlspecialchars(trim($fuseau)); // fuseau horaire
    $interval = (!is_bool($interval)) ? true : $interval; // Specifie si on gere les intervales ou pas
    $interval = ($interval == false) ? false : true; // Specifie si on gere les intervales ou pas

    $date = new DateTime($date);  // On contruit la date

    // Si on ne gere pas les intervales
    if ($interval == false) {
        //On renvoie la date formatée et dans le fuseau correspondant
        return $date->setTimezone(new DateTimeZone($fuseau))->format($format);
    }

    // Si on gere les intervales
    $maintenant = new DateTime(date('Y-m-d H:i:s')); // recupere la date actuelle

    // On place les date dans le fuseau horaire souhaité
    $maintenant->setTimezone(new DateTimeZone($fuseau));
    $date->setTimezone(new DateTimeZone($fuseau));

    $dif = $maintenant->diff($date)->format('%d');
    if ($dif < 0 OR $dif > 3) {
        // Si sa fait plus de 3 jours ou la date n'est pas encore arriver
        $return = $date->setTimezone(new DateTimeZone($fuseau))->format($format);
    } else if ($dif > 1) {
        $return = $maintenant->diff($date)->format('There is %d days');
    } else if ($dif == 1) {
        $return = 'Yesterday at ' . $date->format('H') . 'h';
    } else {
        // Si c'est aujourd'hui
        $dif = $maintenant->diff($date)->format('%h');
        if ($dif < 0) {
            // Si l'heure est par hasard negatif, on renvoie la data normalement
            $return = $date->setTimezone(new DateTimeZone($fuseau))->format($format);
        } else if ($dif > 1) {
            $return = $maintenant->diff($date)->format('There is %h hours');
        } else {
            $dif = $maintenant->diff($date)->format('%i');
            if ($dif < 0) {
                $return = $date->setTimezone(new DateTimeZone($fuseau))->format($format);
            } else if ($dif > 1) {
                $return = $maintenant->diff($date)->format('There is a %i minutes');
            } else {
                $dif = $maintenant->diff($date)->format('%s');
                if ($dif < 0) {
                    $return = $date->setTimezone(new DateTimeZone($fuseau))->format($format);
                } else if ($dif >= 10) {
                    $return = $maintenant->diff($date)->format('There is %s seconds');
                } else {
                    $return = 'Now';
                }
            }
        }
    }
    if ($maintenant->diff($date)->format('%y') >= 1 OR $maintenant->diff($date)->format('%m') >= 1) {
        return $date->setTimezone(new DateTimeZone($fuseau))->format($format);
    }
    if ($maintenant->format('d') < $date->format('d') AND $maintenant->format('y-m') <= $date->format('y-m')) {
        return $date->setTimezone(new DateTimeZone($fuseau))->format($format);
    }
    if ($maintenant < $date) {
        $dif = $date->diff($maintenant)->format('%y');
        if ($dif > 1) {
            return $date->diff($maintenant)->format('In %y years');
        }
        $dif = $date->diff($maintenant)->format('%m');
        if ($dif > 1) {
            return $date->diff($maintenant)->format('In %m months');
        }
        $dif = $date->diff($maintenant)->format('%d');
        if ($dif > 1) {
            return $date->diff($maintenant)->format('In %d days');
        }
        $dif = $date->diff($maintenant)->format('%h');
        if ($dif < 0) {
            return $date->setTimezone(new DateTimeZone($fuseau))->format($format);
        } else if ($dif >= 1) {
            return $date->diff($maintenant)->format('In %h hours');
        }
        $dif = $date->diff($maintenant)->format('%i');
        if ($dif < 0) {
            return $date->setTimezone(new DateTimeZone($fuseau))->format($format);
        } else if ($dif >= 1) {
            return $date->diff($maintenant)->format('In %i minutes');
        } else {
            return $date->setTimezone(new DateTimeZone($fuseau))->format($format);
        }
    }
    return $return;
}


/**
 * ------- FUNCTION SCL_TRANSLATE_DATE()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail>
 * @brief Cette fonction permet de traduire une date en une autre langue formater une date
 * @return string
 *
 * @param string $date la date à traduire
 * @param string> [$lang] la langue de sortie
 */
function scl_translate_date($date, $lang = 'en')
{
    $lang = strtolower($lang);

    if ($lang == 'fr') {
        $date = preg_replace('#^Now?#iSu', 'Maintenant', $date);
        $date = preg_replace('#^There is(\s?a)?#iSu', 'Il y a', $date);
        $date = preg_replace('#^Yesterday at#iSu', 'Hier à', $date);
        $date = preg_replace('#^In#iSu', 'Dans', $date);

        $date = preg_replace('#years#iSu', 'ans', $date);
        $date = preg_replace('#months#iSu', 'mois', $date);
        $date = preg_replace('#days#iSu', 'jours', $date);
        $date = preg_replace('#hours#iSu', 'heures', $date);
        $date = preg_replace('#seconds#iSu', 'secondes', $date);

        $date = preg_replace('#Monday#iSu', 'Lundi', $date);
        $date = preg_replace('#Mon#iSu', 'Lun', $date);
        $date = preg_replace('#Tuesday#iSu', 'Mardi', $date);
        $date = preg_replace('#Tue#iSu', 'Mar', $date);
        $date = preg_replace('#Wednesday#iSu', 'Mercredi', $date);
        $date = preg_replace('#Wed#iSu', 'Mer', $date);
        $date = preg_replace('#Thursday#iSu', 'Jeudi', $date);
        $date = preg_replace('#Thu#iSu', 'Jeu', $date);
        $date = preg_replace('#Friday#iSu', 'Vendredi', $date);
        $date = preg_replace('#Fri#iSu', 'Ven', $date);
        $date = preg_replace('#Saturday#iSu', 'Samedi', $date);
        $date = preg_replace('#Sat#iSu', 'Sam', $date);
        $date = preg_replace('#Sunday#iSu', 'Dimanche', $date);
        $date = preg_replace('#Sun#iSu', 'Dim', $date);

        $date = preg_replace('#January#iSu', 'Janvier', $date);
        $date = preg_replace('#Jan#iSu', 'Jan', $date);
        $date = preg_replace('#February#iSu', 'Févier', $date);
        $date = preg_replace('#Feb#iSu', 'Fev', $date);
        $date = preg_replace('#March#iSu', 'Mars', $date);
        $date = preg_replace('#Mar#iSu', 'Mar', $date);
        $date = preg_replace('#April#iSu', 'Avril', $date);
        $date = preg_replace('#Apr#iSu', 'Avr', $date);
        $date = preg_replace('#May#iSu', 'Mai', $date);
        $date = preg_replace('#June#iSu', 'Juin', $date);
        $date = preg_replace('#Jun#iSu', 'Juin', $date);
        $date = preg_replace('#July#iSu', 'Juillet', $date);
        $date = preg_replace('#July?#iSu', 'Jui', $date);
        $date = preg_replace('#August#iSu', 'Août', $date);
        $date = preg_replace('#Aug#iSu', 'Août', $date);
        $date = preg_replace('#September#iSu', 'Septembre', $date);
        $date = preg_replace('#Sept?#iSu', 'Sept', $date);
        $date = preg_replace('#October#iSu', 'Octobre', $date);
        $date = preg_replace('#Oct#iSu', 'Oct', $date);
        $date = preg_replace('#November#iSu', 'Novembre', $date);
        $date = preg_replace('#Nov#iSu', 'Nov', $date);
        $date = preg_replace('#December#iSu', 'Décembre', $date);
        $date = preg_replace('#Dec#iSu', 'Déc', $date);
    }
    return $date;
}


/**
 * ------- FUNCTION SCL_HASH()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @brief Cette fonction permet d'hasher une chaine
 * @return string
 *
 * @param string $str la chaine caractères qu'on veut hasher
 * @param int [$lenght] la longueur de la chaine a sortir
 */
function scl_hash($str, $lenght = 128, $key = '')
{
// Valeur par defaut
    $lenght = (empty($lenght) OR !is_int($lenght)) ? 128 : $lenght;
//    Valeurs minimale et maximale pour le haché
    $lenght = ($lenght < 50) ? 50 : $lenght;
    $lenght = ($lenght > 200) ? 200 : $lenght;

    $str = trim(htmlspecialchars($str)); // On echappe la chaine et on supprime les espaces de debut et de fin

    $code = strlen($str); // On recupere la logueur de la chaine
    $code = ($code * 200) * ($code / 50); // afin de creer un code

//    Le sel
    $sel1 = strlen($str);
    $sel2 = strlen($code);
    $sel = strlen($str . $code);

//    Le hashage commence ici
    $texte_hash_1 = hash('sha256', 'scl_' . $sel1 . $str . $sel2); // Premier hash avec le sha256
    $texte_hash_2 = hash('sha512', $texte_hash_1 . $sel . '_hash'); // Deuxieme hash avec le sha512

//    On divise les deux hash en 2 parties egales
    $texte_hash_1_1 = substr($texte_hash_1, 0, 32);
    $texte_hash_1_2 = substr($texte_hash_1, -32); // Les parties du premier hashé
    $texte_hash_2_1 = substr($texte_hash_2, 0, 64);
    $texte_hash_2_2 = substr($texte_hash_2, -64); // Les parties du deuxieme hashé

    $final = $texte_hash_1_1 . 'ec' . $texte_hash_2_1 . 'af' . $texte_hash_1_2 . '84' . $texte_hash_2_2 . '5f'; // On additionne les deux hashés en ajoutant un sel statique pour atteindre les 200 caract

//    On renvoi le hash avec la longueur souhaité
    return substr($final, 0, $lenght);
}


/**
 * ------- FUNCTION SCL_CYPHER()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @brief Cette fonction permet d'encrypter et de decrypter une chaine de caracteres
 * @return string
 *
 * @param string $str la chaine caractères qu'on veut encrypter ou decrypter
 * @param string [$action] l'action qu'on veut effectuer (encrypter/decrypter).
 * @param int [$repeat] le nombre de fois qu'on repete l'action
 */
function scl_cypher($str, $action = 'encrypt', $repeat = 0)
{
    /*Valeur par defaut */
    $action = strtolower($action);
    $action = ($action != 'decrypt') ? 'encrypt' : 'decrypt';
    $repeat = (!is_int($repeat) OR $repeat < 1) ? 0 : $repeat;

    $chars = ''; //Les differents caractères entrés
    $size = strlen($str);

    for ($i = $size; $i > 1; $i--) {
        // On separes chaque caracteres du mot independament
        $chars .= substr($str, 0, 1) . ' ';
        $str = substr($str, 1);
    }
    if (strlen($str) <> 0) {
        $chars .= $str;
    } else {
        $chars = substr($chars, 0, strlen($chars) - 1);
    }

    //Les entrées / sorties
    $inputs = array(
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
    );
    $outputs = array(
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
        'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
    );
    $chars = explode(' ', $chars);  // On transforme la chaine précédement créée en tableau
    $return = '';

    // On parcours tout le dit tableau (ie. on recupere les caracteres un a un)
    foreach ($chars As $val) {
        // Si le caractère est encryptable ou decryptable
        if (in_array($val, $inputs) AND in_array($val, $outputs)) {
            // Si on veut encrypter
            if ($action == 'encrypt') {
                $num = array_search($val, $inputs); // On recupere l'index de la lettre
                $return .= $outputs[$num]; // et on ajoute le caractère d'encryptage correspondant
            } // sinon, on veut decrypter
            else {
                $num = array_search($val, $outputs); // On recupere l'index de la lettre
                $return .= $inputs[$num]; // et on ajoute le caractère de décryptage correspondant
            }
        } //Sinon
        else {
            $return .= $val; // On le laisse comme il est
        }
    }

    for ($i = 0; $i < $repeat; $i++) {
        $return = scl_cypher($return, $action);
    }

    return $return; // On renvoie la chaine encrypter ou decrypter
}


/**
 * ------- FUNCTION SCL_CRYPT()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @param string $str la chaine caractères qu'on veut e
 * @brief Cette fonction permet d'encrypter et de decrypter une chaine de caracteres
 * @return string
 *ncrypter ou decrypter
 * @param string $key la clé de chiffrement
 * @param string [$action] l'action qu'on veut effectuer (encrypter/decrypter).
 */
function scl_crypt($str, $key, $action = 'encrypt')
{
    /*Valeur par defaut */
    $action = strtolower($action);
    $action = ($action != 'decrypt') ? 'encrypt' : 'decrypt';

    $return = ''; // La valeur retournée

    /* On protège la clé pour eviter les failles */
    $code = strlen($key);
    $code = ($code * 4) * ($code / 3);
    $key = sha1(strlen($key) . $key . strlen($key . $code));
    $key .= md5($code . $key . $code);

    $letter = -1;
    $str = ($action == 'encrypt') ? $str : base64_decode($str);
    $strlen = strlen($str); // Nombre de caractère de la chaine à traiter

    for ($i = 0; $i < $strlen; $i++) {
        $letter++;
        if ($letter > 31) {
            $letter = 0;
        }
        if ($action == 'encrypt') {
            $neword = ord($str[$i]) + ord($key[$letter]);
            if ($neword > 255) {
                $neword -= 256;
            }
        } else {
            $neword = ord($str[$i]) - ord($key[$letter]);
            if ($neword < 1) {
                $neword += 256;
            }
        }
        $return .= chr($neword);
    }
    $return = ($action == 'encrypt') ? base64_encode($return) : $return;
    return $return; // On renvoie la chaine encrypter ou decrypter
}


/**
 * ------- FUNCTION SCL_TRUNCATE()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @brief Cette fonction permet de couper une chaine de caractere
 * @return string
 *
 * @param  string $str la chaine caractères qu'on veut couper
 * @param int $size est la longueur finale de la chaine apres la coupure
 * @param bool [$suspension] specifie si on ajoute les points de suspension à la fin (true) ou pas (false).
 */
function scl_truncate($str, $size, $suspension = false)
{
    /*Valeur par defaut */
    $str = htmlspecialchars($str); // On protege la chaine
    $size = (!is_numeric($size)) ? strlen($str) : $size; // Taille à couper

    $lenght = strlen($str); // longueur de la chaine
    if ($lenght > $size) {
        // Si la longueur initiale de la chaine est superieur à la taille qu'on veut
        $str = substr($str, 0, $size); // On coupe la chaine
        if ($suspension === true) {
            $str .= '...'; // On ajoute les suspension
        }
    }
    return $str; // On renvoie la chaine couper
}


/**
 * ------- FUNCTION SCL_SHORTENSTR()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @brief Cette fonction permet de couper une chaine de caractere
 * @return string
 *
 * @param  string $str la chaine caractères qu'on veut couper
 * @param int $max est la longueur finale de la chaine apres la coupure
 * @param string [$sep] Le separateur entre le debut et la fin de la chaine
 * @param int [$width] Le nombre de fois qu'on doit repeter le separateur
 *
 * @example
 *  echo scl_shortenStr("J'aime le PHP et j'ose croire que cette fonction vous sera utile", 34); // J'aime le PHP et...vous sera utile
 */
function scl_shortenStr($str, $max, $sep = '.', $width = 3)
{
    /*Valeur par defaut */
    $str = htmlspecialchars($str); // On protege la chaine
    $max = (!is_numeric($max)) ? strlen($str) : $max; // Taille à couper

    $length = strlen($str); // Nombre de caractères

    if ($length > $max) {
        $too_much_length = $length - $max + $width; // Nombre de caractères en trop
        if ($max < $width) {
            // Dans le cas où la largeur max est inférieur à la largeur du séparateur
            $width = $max;
        }
        $start = ceil($length / 2 - $too_much_length / 2);
        $str = substr($str, 0, $start) . str_repeat($sep, $width) . substr($str, floor($start + $too_much_length));
    }
    return $str;
}


/**
 * ------- FUNCTION SCL_CLEANER()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *
 * @brief Cette fonction permet de suprimer les espace en debut et fin d'une chaine tout en echappant les entités html
 * @return string
 *
 * @param string $str la chaine caractères qu'on veut netoyer
 */
function scl_cleaner(&$str)
{
    if (is_array($str)) {
        foreach ($str As $key => $value) {
            if (is_string($value)) {
                $str[$key] = trim(htmlspecialchars($value));
            }
            if (is_array($value)) {
                foreach ($value As $cle => $valeur) {
                    $str[$key][$cle] = scl_cleaner($valeur);
                }
            }
        }
    }
    if (is_string($str)) {
        $str = trim(htmlspecialchars($str));
    }
    return $str; // On renvoie la chaine netoyer
}


/**
 * ------- FUNCTION SCL_INCLUDE()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *
 * @brief Cette fonction permet inclure un fichier de maniere securiser dans une page web
 * @return void
 *
 * @param string $file le fichier a inclure
 * @param array|null $data
 * @param bool $exception
 *
 */
function scl_include($file, $data = array(), $exception = false)
{
    $file = trim(htmlspecialchars($file));
    if (is_file($file) AND file_exists($file)) {
        extract((array)$data);
        include_once($file);
    }
    else if ($exception) {
        die('SCL EXCEPTION : The file &laquo; <b>' . $file . '</b> &raquo; don\'t exist !');
    }
    return;
}


/**
 * ------- FUNCTION SCL_DEBUG()   --------
 * @author Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 *
 * @brief Cette fonction permet de debuger une ou plusieurs variable
 * @return void
 *
 * @param mixed $var
 * @param bool $style specifie si on veut styliser le resultat ou pas (affichage classique de var_dump())
 */
function scl_debug($var, $style = false)
{
    $vars = (array) $var;

    if(true !== (bool) $style)
    {
        echo "<pre style=\"background:#eee;padding:1em;border:1px inset #adb5bd;border-radius:5px;font-family:monospace;margin-top:0;margin-bottom:1rem;overflow:auto;-ms-overflow-style:scrollbar;\">\n";
        foreach ($vars As $var) {
            var_dump($var);
        }
        echo "</pre>\n";
        return;
    }

    echo "<pre style=\"background:#eee;padding:1em;border:1px inset #adb5bd;border-radius:5px;font-family:monospace;margin-top:0;margin-bottom:1rem;overflow:auto;-ms-overflow-style:scrollbar;\">\n";
    $i = 0;

    foreach ($vars As $var)
    {
        if($i != 0) {
            echo '<hr style="height:1;margin:0;margin-top:1em;" color="#ddd" size="1">';
        }
        $i++;

        echo '<span style="width:auto;display:inline-block; margin-bottom: .25em; font-weight:bold;font-family:\'Lato\', candara, \'Arial Narrow\', sans-serif; color:#6c17cb; font-style:italic">';
        echo ucfirst(gettype($var));
        if(is_object($var)) {
            echo ' | '.get_class($var);
        }

        if(is_string($var)) {
            echo '<span style="width:auto;font-weight:lighter !important; display:inline-block; padding-left:.5em; color:crimson">(lenght : '.strlen($var).')</span>';
        }
        if(is_array($var)) {
            echo '<span style="width:auto;font-weight:lighter !important; display:inline-block; padding-left:.5em; color:crimson">(lenght : '.count($var).')</span>';
        }
        echo "</span>";

        if(is_null($var)) {
            echo "\n\tNULL";
        }
        else if(is_string($var) OR is_numeric($var))
        {
            echo "\n\t".$var;
        }
        else if(is_bool($var)) {
            echo (true === $var) ? "\n\ttrue" : "\n\tfalse";
        }
        else if(is_array($var) OR is_object($var))
        {
            if(empty($var)) {
                echo "\n\tempty";
            }
            else
            {
                foreach($var As $key => $value)
                {
                    echo "\n\t<span style=\"width:auto;display:inline-block; margin: .25em; 0\">";
                    echo "<b>".$key."</b> => ";
                    if(is_array($value) OR is_object($value)) {
                        scl_debug($value);
                    }
                    else {
                        if(is_bool($value)) {
                            echo (true === $value) ? 'true' : 'false';
                        }
                        else {
                            echo $value;
                        }
                        echo '<span style="auto;font-weight:lighter !important; display:inline-block; padding-left:.5em; color:#eb7c55">['.ucfirst(gettype($value));
                        if(is_string($value)) {
                            echo '('.strlen($value).')';
                        }
                        echo ']</span>';
                    }
                    echo "</span>";
                }
            }
        }
    }
    echo "</pre>";
    return;
}




/* ------------------------------------------------------------------------- */


/**
 * ------- FUNCTION SCL_BYTE2SIZE()   --------
 *
 * @brief Cette fonction donne le poids en byte, kb, mb en fonction du nombre de byte passé en parametre
 * @return string
 *
 * @param int $bytes
 * @param int $format
 * @param int $precision
 */
function scl_byte2size($bytes, $format = 1024, $precision = 2)
{
    $unit = array('B','KB','MB');
    return @round($bytes / pow($format, ($i = floor(log($bytes, 1024)))), $precision).' '.$unit[$i];
}



/**
 * ------- FUNCTION SCL_MOVESPECIALCHAR()   --------
 *
 * @brief Cette fonction permet d'enlever tous les caractères spéciaux (accents, points...) dans une chaine
 * @return string
 *
 * @param string $str est chaine à traiter.
 * @param bool [$leaveSpecialChar] specifie si on veut laisser les ponctuations (.,?/:) (true) ou les remplacer par les tirets (false)
 * @param bool [$UpperToLower] specifie si on veut laisser les majuscules (false) ou les transformer en minuscules (true)
 * @param array [$specialChars] La liste des caracteres speciaux a gerer
 */
function scl_moveSpecialChar($str, $leaveSpecialChar = false, $UpperToLower = true, $specialChars = array())
{
    /* Valeurs par défaut */
    $leaveSpecialChar = ($leaveSpecialChar != false) ? true : false;
    $UpperToLower = ($UpperToLower != false) ? true : false;

    // transformer les caractères accentués en entités HTML
    $str = htmlentities($str, ENT_NOQUOTES);
    // remplacer les entités HTML pour avoir juste le premier caractères non accentués. Exemple : "&ecute;" => "e", "&Ecute;" => "E", "à" => "a" ...
    $str = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    // Remplacer les ligatures tel que : ?, Æ ... . Exemple "œ" => "oe"
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
    // Supprimer tout le reste
    $str = preg_replace('#&[^;]+;#', '', $str);

    // Si on ne veut pas avoir les caractères spéciaux
    if ($leaveSpecialChar == false) {
        // Caractères spéciaux à modifier en tirets
        $specialChars = array_merge(array(
            ' ', '?', '!', '.', ',', ':', ';', "'", '"', '/', '\\', '*', '+', '=', '%',
            '[', ']', '(', ')', '{', '}', '<', '>', '&', '$', '^', '@', 'µ', '£', '§',
        ), $specialChars);
        $str = str_replace($specialChars, '-', $str);
    }
    $modifStr = true;
    while ($modifStr == true) {
        $modifStr = false;
        // On modifie tous les << -- >> par un seul << - >>
        if (preg_match('#--#iSu', $str)) {
            $str = str_replace('--', '-', $str);
            $modifStr = true;
        }
        // Si le dernier caractère est un tiret, on le supprime
        if (preg_match('#(-)+$#iSu', $str)) {
            $str = substr($str, 0, (strlen($str) - 1));
            $modifStr = true;
        }
    }
    if ($UpperToLower == true) {
        // Si on veut transformer les majuscules en minuscules
        $str = strtolower($str);
    }
    return $str;
}


/**
 * ------- FUNCTION SCL_MOVEDUPLICATECHAR()   --------
 *
 * @brief Cette fonction permet d'enlever tous les caractères doublées dans une chaine
 * @return string
 *
 * @param string $str est la chaine à traiter.
 */
function scl_moveDuplicateChar($str)
{
    for ($ascii = 95; $ascii <= 123; $ascii++) {
        // le chiffre 3 permet de controler qu'il y a 3 fois de suite le meme caractère
        $str = preg_replace('#' . chr($ascii) . '{3,}#', chr($ascii), $str);
    }
    return $str;
}


/**
 * ------- FUNCTION SCL_SPLITINT()   --------
 *
 * @brief Cette fonction permet de segmenter un nombre
 * @return string|void
 *
 * @param float|int $nbr le nombre qu'on veut segmenter.
 * @param int [$pas] le pas de segmentation.
 * @param string [$separateur] le separateur des segments.
 */
function scl_splitInt($nbr, $pas = 3, $separateur = ' ')
{
    /* Valeurs par défaut */
    if (!is_numeric($nbr)) {
        return;
    }
    $pas = (!empty($pas) AND is_int($pas)) ? $pas : 3;
    $separateur = (in_array($separateur, array(' ', ',', '-', '/'))) ? $separateur : ' ';

    $return = ''; // Valeur renvoyée

    $nombre = explode('.', $nbr);
    $nbr = $nombre[0];
    $virgule = (!empty($nombre[1])) ? '.' . $nombre[1] : '';

    $lenght = strlen($nbr); // nombre de chiffre
    $nbr = strrev($nbr); // on inverse le nombre

    while ($lenght > $pas) {
        // On coupe le nombre après 3 ($pas) chiffres à partir de debut et on le stocke dans $return
        $return .= substr($nbr, 0, $pas) . $separateur;
        $nbr = substr($nbr, $pas); // On enleve les 3 ($pas) premier chiffres du nombre
        $lenght = $lenght - $pas; // Le nombre de chiffr du nombre diminue donc de 3 ($pas)
    }
    if (strlen($nbr) <> 0) {
        $return .= $nbr;
    } else {
        $return = substr($return, 0, strlen($return) - 1);
    }
    // On inverse encore le nombre pour revenir à la valeur initiale et on le retourne
    return strrev($return) . '' . $virgule;
}


/**
 * ------- FUNCTION SCL_GETTAGS()   --------
 *
 * @brief Cette fonction permet de generer les mots clés d'un contenu
 * @return string
 *
 * @param string $content le contenu à partir duquel on va generer les mots clés.
 * @param int [$nb_tags]  le nombre de mots clés à generer.
 * @param bool [$relief] specifie si les mots clés renvoyés doivent avoir une taille proportionnelle à leur frequence d'apparition (true) ou pas (false).
 * @param null|file|array [$mots_a_banir] specifie une liste de mot a banir dans la recherche
 */
function scl_getTags($content, $nb_tags = 10, $relief = false, $mots_a_bannir = null)
{
    /* Valeurs par défaut */
    $nb_tags = (empty($nb_tags)) ? 10 : (int)$nb_tags;
    $relief = ($relief != true) ? false : true;

    if (is_file($mots_a_bannir) AND file_exists($mots_a_bannir)) {
        $mots_a_bannir = file_get_contents($mots_a_bannir);
        $mots_a_bannir = explode("\n", $mots_a_bannir);
    }
    $mots_a_bannir = array_merge(array(
        #---------------Pronoms---------------
        'ELLE', 'ELLES', 'CETTE', 'IL', 'ILS', 'LUI', 'NOUS', 'VOUS', 'EUX', 'MOI', 'JE', 'TU',

        #---------------Verbes etre et avoir---------------
        'SOMMES', 'ETES', 'SONT', 'ETAIS', 'ETAIS', 'ETAIT', 'ETIONS', 'ETIEZ', 'ETAIENT', 'FUMES', 'FUTES',
        'FURENT', 'SERAI', 'SERAS', 'SERA', 'SERONS', 'SEREZ', 'SERONT', 'SOIS', 'SOIT', 'SOYONS', 'SOYEZ',
        'SOIENT', 'FUSSE', 'FUSSES', 'FUT', 'FUSSIONS', 'FUSSIEZ', 'FUSSENT', 'AVONS', 'AVEZ', 'AVAIS',
        'AVAIT', 'AVIONS', 'AVIEZ', 'AVAIENT', 'AURAI', 'AURAS', 'AURA', 'AURONS', 'AUREZ', 'AURONT',
        'AURAIS', 'AURAIS', 'AURAIT', 'AURIONS', 'AURIEZ', 'AURAIENT',

        #---------------Mot de liaison---------------
        'DANS', 'AVEC', 'AFIN', 'POUR', 'DONT', 'COMME', 'SELON', 'APRES', 'ENSUITE', 'QUAND', 'QUANT',
        'PUIS', 'ENFIN', 'MAIS', 'CEPENDANT', 'TOUTEFOIS', 'NEANMOINS', 'POURTANT', 'SINON', 'SEULEMENT',
        'MALGRE', 'QUOIQUE', 'TANDIS', 'ALORS', 'CERTES', 'EVIDEMMENT', 'EVIDENT', 'AINSI', 'SOIT', 'DONC',
        'TOUT', 'TOUS',

        #---------------Caracteres HTML---------------
        'AGRAVE', 'EACUTE', 'NBSP',
    ), (array)$mots_a_bannir);

    foreach ($mots_a_bannir As $banni) {
        $content = preg_replace('#' . $banni . '#iSu', '', $content); //Supprime les mots à bannir
    }
    $content = preg_replace("#[^[:alpha:]]#", ' ', $content); //On ne garde que les chaines contenant des caractères
    $content = preg_replace('#(\s|\b)[\w]{1,3}\s#i', ' ', $content); //On supprime les chaines inférieurs à 3 caractrèes
    $content = preg_replace('/\s\s+/', ' ', $content); //Supprime les doubles espaces

    $content = explode(" ", $content); // On recupere chaque mot (separer de l'autre mot par un espace)
    $nb_words = count($content); // On compte le nombre de mots du contenu

    $content = array_count_values($content); //Construit un tableau avec le nombre d'occurences de chaques mots
    arsort($content); //Tri le tableau par valeur décroissante en gardant les index
    $tags = array_slice($content, 0, $nb_tags, true); //Coupe le tableau pour n'obtenir que le nombre de mots souhaité

    foreach ($tags as &$value) { //Utilisation de & pour modifier la valeur
        $value = round((($value / $nb_words) * 100), 2);
    }
    $render_array = array();
    foreach ($tags as $key => $value) {
        // Si on veut voir en relief c'est-a-dire avec des taille qui dependent du nombre d'apparition des tags
        if ($relief == true) {
            $size = 6;
            $size = $size + $size * $value; //Calcul de la taille du tag en fonction de sa fréquence d'apparition
            $render_array[] = "<span style='font-size:" . $size . "px' class='tag'>" . strtoupper($key) . "</span> ";
        } else {
            $render_array[] = $key . " ";
        }
    }
    srand((float)microtime() * 1000000);
    shuffle($render_array); //On mélange aléatoirement le tableau
    $return = ''; // La variable de retour
    foreach ($render_array as $value) {
        $return .= $value;
    }
    return $return;
}


/**
 * ------- FUNCTION SCL_INT2LETTER()   --------
 *
 * @brief Cette fonction convertie un entier en lettre (ex: 12 => douze)
 * @credit { Author: zied9b | Url: http://codes-sources.commentcamarche.net/source/51285-chiffres-en-lettres}
 * @return string
 *
 * @param int $int
 */
function scl_int2letter($int)
{
    $int_val= intval($int);
    $real_val = intval(round($int - intval($int), 2) * 1000);

    if($int_val == 0) {
        return 'Zero';
    }
    $return = '';

    $dix_c = intval($real_val%100/10);
    $cent_c = intval($real_val%1000/100);
    $unite[1] = $int_val%10;
    $dix[1] = intval($int_val%100/10);
    $cent[1] =intval($int_val%1000/100);
    $unite[2]=intval($int_val%10000/1000);
    $dix[2] = intval($int_val%100000/10000);
    $cent[2] = intval($int_val%1000000/100000);
    $unite[3] = intval($int_val%10000000/1000000);
    $dix[3] = intval($int_val%100000000/10000000);
    $cent[3] = intval($int_val%1000000000/100000000);

    $chif = array('', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf', 'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix sept', 'dix huit', 'dix neuf');

    $trio_c='';
    for($i=1; $i<=3; $i++)
    {
        $prim[$i]='';
        $secon[$i]='';
        $trio[$i]='';

        if($dix[$i]==0) {
            $secon[$i]=''; $prim[$i]=$chif[$unite[$i]];
        }
        else if($dix[$i]==1) {
            $secon[$i]=''; $prim[$i]=$chif[($unite[$i]+10)];
        }
        else if($dix[$i]==2)
        {
            if($unite[$i]==1) {
                $secon[$i]='vingt et'; $prim[$i]=$chif[$unite[$i]];
            }
            else {
                $secon[$i]='vingt'; $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==3)
        {
            if($unite[$i]==1) {
                $secon[$i]='trente et'; $prim[$i]=$chif[$unite[$i]];
            }
            else {
                $secon[$i]='trente'; $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==4)
        {
            if($unite[$i]==1) {
                $secon[$i]='quarante et'; $prim[$i]=$chif[$unite[$i]];
            }
            else {
                $secon[$i]='quarante'; $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==5)
        {
            if($unite[$i]==1) {
                $secon[$i]='cinquante et'; $prim[$i]=$chif[$unite[$i]];
            }
            else {
                $secon[$i]='cinquante'; $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==6)
        {
            if($unite[$i]==1) {
                $secon[$i]='soixante et'; $prim[$i]=$chif[$unite[$i]];
            }
            else {
                $secon[$i]='soixante'; $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==7)
        {
            if($unite[$i]==1) {
                $secon[$i]='soixante et'; $prim[$i]=$chif[$unite[$i]+10];
            }
            else {
                $secon[$i]='soixante'; $prim[$i]=$chif[$unite[$i]+10];
            }
        }
        else if($dix[$i]==8)
        {
            if($unite[$i]==1)
            {
                $secon[$i]='quatre-vingts et'; $prim[$i]=$chif[$unite[$i]];
            }
            else {
                $secon[$i]='quatre-vingt'; $prim[$i]=$chif[$unite[$i]];
            }
        }
        else if($dix[$i]==9)
        {
            if($unite[$i]==1) {
                $secon[$i]='quatre-vingts et'; $prim[$i]=$chif[$unite[$i]+10];
            }
            else {
                $secon[$i]='quatre-vingts'; $prim[$i]=$chif[$unite[$i]+10];
            }
        }

        if($cent[$i]==1) {
            $trio[$i]='cent';
        }
        else if($cent[$i]!=0 || $cent[$i]!='') {
            $trio[$i] = $chif[$cent[$i]] .' cents';
        }
    }


    $chif2=array('', 'dix', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante-dix', 'quatre-vingts', 'quatre-vingts dix');

    $secon_c=$chif2[$dix_c];
    if($cent_c==1) {
        $trio_c='cent';
    }
    else if($cent_c!=0 || $cent_c!='') {
        $trio_c = $chif[$cent_c] .' cents';
    }


    if(($cent[3]==0 || $cent[3]=='') && ($dix[3]==0 || $dix[3]=='') && ($unite[3]==1)) {
        $return .= $trio[3]. '  ' .$secon[3]. ' ' . $prim[3]. ' million ';
    }
    else if(($cent[3]!=0 && $cent[3]!='') || ($dix[3]!=0 && $dix[3]!='') || ($unite[3]!=0 && $unite[3]!='')) {
        $return .= $trio[3]. ' ' .$secon[3]. ' ' . $prim[3]. ' millions ';
    }
    else {
        $return .= $trio[3]. ' ' .$secon[3]. ' ' . $prim[3];
    }

    if(($cent[2]==0 || $cent[2]=='') && ($dix[2]==0 || $dix[2]=='') && ($unite[2]==1)) {
        $return .= ' mille ';
    }
    else if(($cent[2]!=0 && $cent[2]!='') || ($dix[2]!=0 && $dix[2]!='') || ($unite[2]!=0 && $unite[2]!='')) {
        $return .= $trio[2]. ' ' .$secon[2]. ' ' . $prim[2]. ' milles ';
    }
    else {
        $return .= $trio[2]. ' ' .$secon[2]. ' ' . $prim[2];
    }

    $return .= $trio[1]. ' ' .$secon[1]. ' ' . $prim[1];


    if(!($cent_c=='0' || $cent_c=='') || !($dix_c=='0' || $dix_c=='')) {
        $return .= $trio_c. ' ' .$secon_c. ' ' . $dev2;
    }

    return trim($return);
}
