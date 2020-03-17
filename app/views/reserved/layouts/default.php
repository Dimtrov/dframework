<?php
use dFramework\core\output\Layout;

$current_section = trim(str_replace(trim(site_url(), '/'), '', current_url()), '/');

?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $df_pageTitle . ' | Dimtrov Sarl.'; ?></title>
    <link rel="icon" href="<?= img_url('logos/dimtrov.min.png'); ?>" />

    <?php Layout::stylesBundle(); ?>

    <!-- Tweaks for older IEs-->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<!--==========================
Header
============================-->
<header id="header">
    <div class="container">

        <div id="logo" class="pull-left">
            <a href="<?= site_url(); ?>"><img src="<?= img_url('logos/dimtrov.min.png'); ?>" class="img-fluid img-thumbnail w-25" alt="" title="" /></a>
            <!-- Uncomment below if you prefer to use a text logo -->
            <!--<h1><a href="#hero">Regna</a></h1>-->
        </div>

        <nav id="nav-menu-container">
            <ul class="nav-menu">
                <li class="<?= ($current_section == '' OR $current_section == 'home') ? 'menu-active' : ''; ?>"><a href="<?= site_url(); ?>">Accueil</a></li>
                <li class="<?= (preg_match('#^works/?#i', $current_section)) ? 'menu-active' : ''; ?>"><a href="<?= site_url('works'); ?>">Nos travaux</a></li>
                <li class="menu-has-children"><a href="#">Nos services</a>
                    <ul>
                        <li><a href="<?= site_url('jumpto/dwa'); ?>">Dimtrov Web Agency</a></li>
                        <li><a href="<?= site_url('jumpto/dam'); ?>">Dimtrov App Market</a></li>
                    </ul>
                </li>
                <li class="menu-has-children"><a href="#">Nos plateformes</a>
                    <ul>
                        <li><a href="<?= site_url('jumpto/dot'); ?>">Open Training</a></li>
                        <li><a href="<?= site_url('jumpto/dhe'); ?>">House Exchange</a></li>
                    </ul>
                </li>
                <li class="<?= ($current_section == 'home/about') ? 'menu-active' : ''; ?>"><a href="<?= site_url('home/about'); ?>">A propos</a></li>
                <li class="<?= ($current_section == 'home/contact') ? 'menu-active' : ''; ?>"><a href="<?= site_url('home/contact'); ?>">Contact</a></li>
            </ul>
        </nav><!-- #nav-menu-container -->
    </div>
</header><!-- #header -->

<?php Layout::output(); ?>

<!--==========================
  Footer
============================-->
<footer id="footer">
    <div class="footer-top">
        <div class="container">
            <div class="row">

                <div class="col-lg-4 col-md-6 footer-info">
                    <h3>Dimtrov</h3>
                    <p>
                        Agence de création de solutions informatique basées sur le web.
                        Nous mettons notre expertise en oeuvre pour concevoir et réaliser des
                        produits répondants aux problèmes rencontrés de nos jours.
                    </p>
                </div>

                <div class="col-lg-2 col-md-6 footer-links">
                    <h4>Liens utiles</h4>
                    <ul>
                        <li><a href="<?= site_url('home/about'); ?>">A propos de nous</a></li>
                        <li><a href="<?= site_url('home/contact'); ?>">Nous Contacter</a></li>
                        <li><a href="<?= site_url('home/joinus'); ?>">Rejoindre notre équipe</a></li>
                        <li><a href="<?= site_url('licenses'); ?>">Nos licences d'utilisations</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 footer-contact">
                    <h4>Retrouvez nous</h4>
                    <p class="mb-2">
                        No Names City, Awae Escallier <br>
                        Yaoundé - Cameroun <br>
                        <strong>Tel:</strong> (+237) 691 88 95 87 / 673 40 66 61<br>
                        <strong>Email:</strong> groupedimtrov@gmail.com<br>
                    </p>
                    <div class="social-links">
                        <a target="_blank" href="https://twitter.com/Dimtrov" class="twitter"><i class="fab fa-twitter"></i></a>
                        <a target="_blank" href="https://www.facebook.com/dimtrov.officiel" class="facebook"><i class="fab fa-facebook"></i></a>
                        <a target="_blank" href="https://github.com/Dimtrov" class="github"><i class="fab fa-github"></i></a>
                        <a target="_blank" href="mailto:groupedimtrov@gmail.com" class="google-plus"><i class="fa fa-envelope"></i></a>
                    </div>

                </div>

                <div class="col-lg-3 col-md-6 footer-newsletter">
                    <h4>Newsletter</h4>
                    <p>Abonnez-vous à notre newsletter pour rester à l'écoute de nos activités et être informé lors de la sortie d'un nouveau projet.</p>
                    <form action="<?= site_url('home/newsletter'); ?>" id="subscritetonewsletter" method="post">
                        <input type="email" name="email"><input type="submit" value="S'abonner" />
                    </form>
                </div>

            </div>
        </div>
    </div>

    <div class="container">
        <div class="copyright">
            &copy; Copyright 2019 <strong>Dimtrov Sarl</strong>. Tous droits réservés
        </div>
        <div class="credits">
            <!--
              All the links in the footer should remain intact.
              You can delete the links only if you purchased the pro version.
              Licensing information: https://bootstrapmade.com/license/
              Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/buy/?theme=NewBiz
            -->
        </div>
    </div>
</footer><!-- #footer -->

<a href="#" class="back-to-top"><i class="fa fa-chevron-up"></i></a>


    <?php Layout::scriptsBundle(); ?>

</body>
</html>