<?php use dFramework\core\output\Layout; ?>
<?php
Layout::addCss('default/about.index');
?>

<!--==========================
  Hero Section
============================-->
<section class="hero-wrap hero-wrap-2"
         style="background-image: url('<?= img_url('backgrounds/counters-bg.jpg'); ?>');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row slider-text align-items-end justify-content-center">
            <div class="col-md-9  pb-5 text-center">
                <h1 class="mb-3 text-primary text-capitalize">A propos</h1>
                <p class="breadcrumbs font-weight-bold text-black-50">
                    <span><a href="<?= site_url(); ?>">Accueil</a> <i class="fa fa-arrow-right"></i></span>
                    <span class="text-white-50">A propos</span>
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
                <div class="row about-container">
                    <div class="col-lg-6 content order-2">
                        <h2 class="title text-info wow zoomIn">Historique</h2>
                        <p class="text-justify">
                            Créé en juin 2018, Dimtrov est une entreprise de développement de solutions informatique
                            basées sur
                            les technologies web. Notre groupe développe et commercialise des applications fiables
                            répondants aux problématiques
                            du marché. Par ailleurs nous disposons des plateformes communautaire et un service de
                            developpement sur mesure
                            pour vos sites web et applications.
                        </p>
                    </div>
                    <div class="col-lg-6 order-1 wow fadeInLeft banniere mb-3 mb-lg-0"
                         style="background-image: url('<?= img_url('logos/banniere.png'); ?>')">
                        <p class="d-flex align-items-end w-100 h-100">Une plateforme ouverte et entièrement dédiée
                            au web</p>
                    </div>
                </div>
            </div>
        </section><!-- #historique -->

        <!--==========================
          ATOUTS
        ============================-->
        <section class="atouts mt-5">
            <div class="section-header wow zoomIn pt-3">
                <h3 class="section-title">Nos atouts</h3>
                <p class="section-description mb-0">Le groupe Dimtrov possède des valeurs qui font de lui un partenaire de choix</p>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="single-feature text-center px-2 py-3">
                            <i class="fa fa-4x fa-user-tie"></i><br/><br/>
                            <h3 class="text-info">Professionalisme permanent</h3>
                            <p class="pt-3">
                                Nous ne recrutons que des personnes serieuses. Nos employés sont formés pour rester
                                professionnels en toute circonstance et assurer le bon déroulement des activités.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="single-feature text-center px-2 py-3 mt-4 mt-md-0">
                            <i class="fa fa-4x fa-clock"></i><br/><br/>
                            <h3 class="text-info">Respect des délais</h3>
                            <p class="pt-3">
                                Le respect des délais est l'une des principales valeurs de Dimtrov. Nous mettons tout
                                en oeuvre pour toujours livrer les projets dans les délais fixés par nos clients.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="single-feature text-center px-2 py-3 mt-4 mt-lg-0">
                            <i class="fa fa-star fa-4x"></i><br><br>
                            <h3 class="text-info">Diversite de domaine d'activité</h3>
                            <p class="pt-3">
                                Nous n'avons pas de secteur favori. Notre équipe se déploie partout où le besoin se fait
                                ressentir. Informatique, hôtellerie, medecine, commerce, éducation... Nous y sommes.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="single-feature text-center px-2 py-3 mt-4 mt-lg-0">
                            <i class="fa fa-4x fa-smile"></i><br><br>
                            <h3 class="text-info">Satisfaction prioritaire du client</h3>
                            <p class="pt-3">
                                Nous mettons du coeur dans ce que nous faisons. Quelque soit le projet que nous avons devant
                                nous, nous le traitons comme un projet personnel car vous satisfaire est notre priorité.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section><!-- #atouts -->

        <!--==========================
          EQUIPE
        ============================-->
        <section class="equipe mt-5">
            <div class="section-header wow zoomIn pt-3">
                <h3 class="section-title">L'equipe Dimtrov</h3>
                <p class="section-description mb-0">Des jeunes talentieux et pationnés mettent leurs compétences en valeur pour le rayonnement de notre agence</p>
            </div>
            <div class="container">
                <?php if(empty($df_staff)) : ?>
                    <div class="row">
                        <div class="col-lg-6">
                            <img src="<?= img_url('staff/dimitric-sitchet.jpg'); ?>" alt="" class="float-left img-fluid img-thumbnail rounded-circle mr-2" style="width: 10em" />
                            <div>
                                <h4 class="my-0 text-info">Dimitric Sitchet</h4>
                                <h6 class="my-2">President Directeur General</h6>
                                <h6 class="my-0 small text-muted">Ingenieur des travaux informatique - Backend web developer</h6>
                                <p class="mt-4 text-justify">
                                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus adipisci assumenda at commodi deleniti deserunt ea eaque earum illum inventore magni minima nihil odio officiis, optio quasi, veritatis vitae voluptatem!
                                </p>
                            </div>
                        </div>
                    </div>
                <?php else: $cardres = array_splice($df_staff, 0, 4); ?>
                    <div class="row mb-5">
                    <?php foreach ($cardres As $cardre): if(!($cardre instanceof StaffEntity)) continue; ?>
                        <div class="col-lg-6 my-3">
                            <img src="<?= $cardre->avatar(); ?>" alt="" class="float-left img-fluid img-thumbnail rounded-circle mr-2" style="width: 10em; height: 10em" />
                            <div>
                                <h4 class="my-0 text-info"><?= $cardre->profil(); ?></h4>
                                <h6 class="my-2"><?= $cardre->getPoste(); ?></h6>
                                <h6 class="my-0 small text-muted"><?= $cardre->getCompetence(); ?></h6>
                                <p class="mt-4 mb-2 text-justify"><?= $cardre-> getDescription(); ?></p>
                                <a href="<?= $cardre->url(); ?>" class="small">En savoir plus sur <?= $cardre->getPrenomMembre(); ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>

                    <?php if(!empty($df_staff)) : ?>
                    <div class="row mt-5">
                    <?php foreach ($df_staff As $staff): if(!($staff instanceof StaffEntity)) continue; ?>
                        <div class="col-lg-3 border border-light p-2">
                            <div class="row">
                                <div class="col-4"><img src="<?= $staff->avatar(); ?>" alt="" class="img-fluid img-thumbnail rounded-circle" style="width: 5em; height: 5em;" /></div>
                                <div class="col-8">
                                    <h5 class="my-0 text-info"><a href="<?= $staff->url(); ?>"><?= $staff->profil(); ?></a></h5>
                                    <h6 class="my-2"><?= $staff->getPoste(); ?></h6>
                                    <h6 class="my-0 small text-muted"><?= $staff->getCompetence(); ?></h6>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12"><p class="text-justify"><?= $staff-> getDescription(); ?></p></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <!--==========================
          MENTIONS LEGALES
        ============================-->
        <section class="mentions-legales my-5">
            <div class="section-header wow zoomIn pt-3">
                <h3 class="section-title">Mentions legales</h3>
            </div>
            <div class="container mt-4">
                <div class="row">
                    <div class="col-lg-6">
                        <h4>L'entreprise</h4>
                        <dl class="row ml-3 align-items-start">
                            <dt class="col-lg-4 my-2 small">Raison social</dt><dd class="col-lg-7">Dimtrov Sarl</dd>
                            <dt class="col-lg-4 my-2 small">Statut Juridique</dt><dd class="col-lg-7">Société à responsabilité limitée</dd>
                            <dt class="col-lg-4 my-2 small">Siège social</dt><dd class="col-lg-7">Awae Escallier, Yaounde, Cameroun</dd>
                        </dl>
                    </div>
                    <div class="col-lg-6">
                        <h4>Le responsable</h4>
                        <dl class="row ml-3 align-items-start">
                            <dt class="col-lg-4 my-2 small">Nom</dt><dd class="col-lg-7">Sitchet Tomkeu Dimitric</dd>
                            <dt class="col-lg-4 my-2 small">Poste</dt><dd class="col-lg-7">Président Directeur Général</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
