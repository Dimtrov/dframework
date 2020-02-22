<?php
    use dFramework\core\output\Layout;
    Layout::setTitle('dFramework v2.1');
    Layout::addCss('default/works/dframework');
?>

<!--==========================
  Hero Section
============================-->
<section class="hero-wrap hero-wrap-2"
         style="background-image: url('<?= img_url('backgrounds/image-bg1.jpg'); ?>');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row slider-text align-items-end justify-content-center">
            <div class="col-md-9  pb-5 text-center">
                <h1 class="mb-3 text-primary">dFramework</h1>
                <p class="breadcrumbs font-weight-bold text-black-50">
                    <span><a href="<?= site_url(); ?>">Accueil</a> <i class="fa fa-arrow-right"></i></span>
                    <span><a href="<?= site_url('works'); ?>">Nos travaux</a> <i class="fa fa-arrow-right"></i></span>
                    <span class="text-white-50">dFramework</span>
                </p>
            </div>
        </div>
    </div>
</section>


<main id="main">
    <div class="container my-5">
        <!--==========================
          HISTORIQUE
        ============================-->
        <section class="historique">
            <div class="container">
                <div class="row flex-lg-row-reverse">

                    <div class="col-lg-6 wow fadeInRight mb-3 mb-lg-0">
                        <img src="<?= img_url('logos/dframework.png'); ?>" alt="dFramework" class="w-100 h-100"/>
                    </div>
                    <div class="col-lg-6 content">
                        <div class="wow zoomIn">
                            <h2 class="text-primary mb-0">dFramework</h2>
                            <h5 class="text-info">The simplest PHP framework for beginners</h5>
                        </div>
                        <p class="text-justify">
                            dFramerork est un framework de développement d'applications - une boîte à outils - destiné aux personnes qui construisent
                            des applications et sites Web à l'aide de PHP. Son objectif est de vous permettre de développer des projets beaucoup
                            plus rapidement que si vous écriviez du code à partir de rien, en fournissant un ensemble riche de bibliothèques pour les
                            tâches courantes, ainsi qu’une interface simple et une structure logique pour accéder à ces bibliothèques. dFramework vous
                            permet de vous concentrer de manière créative sur votre projet en minimisant la quantité de code nécessaire pour une tâche
                            donnée.
                        </p>
                    </div>
                </div>
            </div>
        </section><!-- #historique -->

        <!--==========================
      Services Section
    ============================-->
        <section id="services" class="mt-3" style="background-color: #d1e2fa">
            <div class="container">
                <header class="section-header">
                    <h3 class="mb-0">Commencez maintenant !</h3>
                    <p>Une prise en main simple, rapide et intuitive.</p>
                </header>

                <div class="row">
                    <div class="col-md-6 col-lg-5 offset-lg-1 wow bounceInUp" data-wow-duration="1.4s">
                        <div class="box">
                            <div class="icon"><i class="fa fa-download " style="color: #ff689b;"></i></div>
                            <h4 class="title"><a>Téléchager les sources</a></h4>
                            <p class="description text-justify">
                                dFramework est un projet libre et gratuit dont vous disposez à tout instant.
                                Téléchargez dès à présent l'archive du projet et commencez à créer votre application.
                                <br><a href="<?= site_url('download/dframework'); ?>" class="d-inline-block font-weight-bold text-center mt-4">Télécharger</a>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-5 wow bounceInUp" data-wow-duration="1.4s">
                        <div class="box">
                            <div class="icon"><i class="fa fa-book-reader" style="color: #e9bf06;"></i></div>
                            <h4 class="title"><a>Lire le guide d'utilisation</a></h4>
                            <p class="description text-justify">
                                Comme pour tout logiciel, la maitrise de dFramework passe par un apprentissage.
                                Lisez notre guide d'utilisation pour voir comment ça marche et bien démarrer votre projet.
                                <br><a target="_blank" href="<?= site_url('docs/dframework/guide/dFramework.html'); ?>" class="d-inline-block font-weight-bold text-center mt-4">Accéder au guide</a>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-5 offset-lg-1 wow bounceInUp" data-wow-delay="0.1s" data-wow-duration="1.4s">
                        <div class="box">
                            <div class="icon"><i class="fa fa-code" style="color: #3fcdc7; margin-left: -10px"></i></div>
                            <h4 class="title"><a>Parcourrir l'API</a></h4>
                            <p class="description text-justify">
                                Le guide d'utilisation est un bon outils pour débuter mais il ne montre pas toutes les fonctionnalités du système.
                                Parcourrez notre API pour voir toute la puissance de notre framework.
                                <br><a target="_blank" href="<?= site_url('docs/dframework/api'); ?>" class="d-inline-block font-weight-bold text-center mt-4">Consulter l'API</a>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-5 wow bounceInUp" data-wow-delay="0.1s" data-wow-duration="1.4s">
                        <div class="box">
                            <div class="icon"><i class="fa fa-comments" style="color:#41cf2e; margin-left: -5px"></i></div>
                            <h4 class="title"><a>Débatre sur le forum</a></h4>
                            <p class="description text-justify">
                                La communauté est un élément clé pour l'essor d'un projet. Si vous avez des préoccupations,
                                n'hésitez pas à les poster sur le forum et si vous avez des solutions, partagez votre expérience.
                                <br><a target="_blank" href="<?= site_url('forum/dframework'); ?>" class="d-inline-block font-weight-bold text-center mt-4">Aller sur le forum</a>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-5 offset-lg-1 wow bounceInUp" data-wow-delay="0.2s" data-wow-duration="1.4s">
                        <div class="box">
                            <div class="icon"><i class="fa fa-code-branch" style="color: #d6ff22;"></i></div>
                            <h4 class="title"><a>Participer au projet</a></h4>
                            <p class="description text-justify">
                                Le projet dFramework est open source. Mettez votre expertise en valeur et aidez nous
                                à améliorer le framework.
                                <br><a target="_blank" href="<?= site_url('contribute/dframework'); ?>" class="d-inline-block font-weight-bold text-center mt-4">Contribuer au développement du projet</a>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-5 wow bounceInUp" data-wow-delay="0.2s" data-wow-duration="1.4s">
                        <div class="box">
                            <div class="icon"><i class="fa fa-chalkboard-teacher" style="color: #4680ff; margin-left: -10px"></i></div>
                            <h4 class="title"><a>Former de nouvelle personne</a></h4>
                            <p class="description text-justify">
                                Lorsque vous serez alaise avec dFramework, n'hésitez pas à former de nouvelles personnes sur son
                                fonctionnement pour le maintenir en vie.
                                <br><a target="_blank" href="<?= site_url('contribute/dframework'); ?>" class="d-inline-block font-weight-bold text-center mt-4">Créer un tutoriel sur Open Training</a>
                            </p>
                        </div>
                    </div>

                </div>

            </div>
        </section><!-- #services -->


        <section class="my-5 py-4 px-2">
            <div class="container">
                <div class="section-header">
                    <h3>Généralités</h3>
                </div>
                <div class="row mt-5">
                    <div class="col-lg-6">
                        <h4 class="text-primary">Historique</h4>
                        <p class="text-justify">
                            dFramework est le diminutif de Dimtrov Framework. Il a été initialement conçu pour aider Dimitri Sitchet à la réalisation
                            de son projet de deuxième année du cycle d'ingénieur à l'Institut Africain d’Informatique représentation du Cameroun.
                        </p>
                        <p class="text-justify">
                            Une fois le projet réalisé, Il a voulu l'utiliser dans ses différents projets futur afin de ne plus avoir à refaire toutes
                            les opérations de routines rencontrées dans la création des sites et applications web (routage, traitement des formulaires, etc)
                            tout en gardant une maîtrise totale de la structure et du fonctionnement du système.
                        </p>
                        <p class="text-justify">
                            Le framework fonctionnait mais avait de gros manquements et était trop basique. Il décida donc de l’améliorer pour
                            l'utiliser en permanance dans ses projets personnels et faire en profiter ses amis de classe (qui n’étaient pas très alaise en développement web).
                            <br>
                            Par ailleurs, etant un partisan de l'initiative GNU et l'open source en général, il s'applique à fond dans la réalisation
                            de cette œuvre pour apporter sa modeste contribution à l’essor de l'informatique en espérant vraiment que son outils
                            pourra être utile à plus d'une personne.
                        </p>
                    </div>
                    <div class="col-lg-6">
                        <h4 class="text-primary">A qui s'adresse dFramework ?</h4>
                        <p class="text-justify mb-3">
                            dFramework n'a nullement l'intention de challenger les frameworks de renommé tels que
                            <a class="font-weight-bold">Symfony</a> ou <a class="font-weight-bold">Laravel</a>.
                            Notre framework a été conçu pour offrir un environnement de travail simplifié. C'est un framework dedié aux
                            débutants en PHP qui souhaitent bénéficier des outils leurs permettant de rapidement mettre en oeuvre leurs
                            projets sans avoir à connaitre toute la complexité des choses.
                            <br>
                            dFramework n'est donc pas recommandé pour des projets lourds. En effet, ce framework est fait pour vous si:
                        </p>
                        <ul style="list-style: url('data:image/gif;base64,R0lGODlhCQAHALMAABQUFDk5OVJSUlpaWmtra3Nzc5ScpcHFzc7W587W79bW1tbe797n9+/v7/f39////ywAAAAACQAHAAAEHbCN0p61CphAb0ZIwR3NtyyIABBfOJYaV1lSdz0RADs=');">
                            <li class="my-1">Vous voulez un cadre avec une petite empreinte.</li>
                            <li class="my-1">Vous avez besoin d'une performance exceptionnelle.</li>
                            <li class="my-1">Vous voulez un framework qui nécessite une configuration presque nulle.</li>
                            <li class="my-1">Vous voulez un framework qui ne vous oblige pas à utiliser la ligne de commande.</li>
                            <li class="my-1">Vous voulez un cadre qui ne vous oblige pas à respecter des règles de codage restrictives.</li>
                            <li class="my-1">Vous n'êtes pas intéressé par les bibliothèques monolithiques à grande échelle comme PEAR.</li>
                            <li class="my-1">Vous ne voulez pas être obligé d'apprendre un langage de templates.</li>
                            <li class="my-1">Vous évitez la complexité, privilégiez les solutions simples.</li>
                            <li class="my-1">Vous avez besoin d'une documentation claire et complète</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
