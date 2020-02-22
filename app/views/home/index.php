<?php
use dFramework\core\output\Layout;

Layout::addCss('default/home');
Layout::addJs('default/home');
Layout::addLibCss(['owlcarousel/owl.carousel.min', 'owlcarousel/owl.theme.default.min']);
Layout::addLibJs('owlcarousel/owl.carousel.min');
Layout::addLibJs(['counterup/jquery.waypoints.min','counterup/jquery.counterup.min']);
?>

<!--==========================
  Hero Section
============================-->
<section id="hero">
    <div class="hero-container">
        <h1>Bienvenue sur <span class="text-primary">DIMTROV</span></h1>
        <h2>Votre plateforme ouverte et entièrement dédiée au web</h2>
        <a href="#about" class="btn-get-started">Commencer</a>
    </div>
</section><!-- #hero -->

<main id="main">

    <!--==========================
      About Us Section
    ============================-->
    <section id="about">
        <div class="container">
            <div class="row about-container">

                <div class="col-lg-6 content order-lg-1 order-2">
                    <h2 class="title text-primary">Quelques mots à propos de nous</h2>
                    <p class="text-justify">
                        Créé en juin 2018, Dimtrov est une entreprise de développement de solutions informatique basées sur
                        les technologies web. Notre groupe développe et commercialise des applications fiables répondants aux problématiques
                        du marché. Par ailleurs nous disposons des plateformes communautaire et un service de developpement sur mesure
                        pour vos sites web et applications.
                    </p>
                    <div class="icon-box wow fadeInUp">
                        <div class="icon text-info"><i class="fa fa-user-tie"></i></div>
                        <h4 class="title"><a href="">Une équipe d'experts</a></h4>
                        <p class="description">Chez Dimtrov, seul les experts sont présents. Nous faire confiance c'est être sûr d'avoir un produit de qualité</p>
                    </div>
                    <div class="icon-box wow fadeInUp" data-wow-delay="0.2s">
                        <div class="icon text-info"><i class="fa fa-star"></i></div>
                        <h4 class="title"><a href="">Une entreprise sociale</a></h4>
                        <p class="description">Dimtrov est une entreprise sociale et donc, le profit nous importe peu. Seule la satisfaction de nos clients et utilisateurs compte pour nous</p>
                    </div>

                    <div class="icon-box wow fadeInUp" data-wow-delay="0.4s">
                        <div class="icon text-info"><i class="fa fa-line-chart"></i></div>
                        <h4 class="title"><a href="">Un groupe d'innovateurs</a></h4>
                        <p class="description">Nous travaillons pour le développement des technologies web. C'est pourquoi, nous créons et distribuons des solutions opensources pour faciliter la mise en oeuvre des pétits et moyens projets web</p>
                    </div>

                </div>

                <div class="col-lg-6 background order-lg-2 order-1 wow fadeInRight"></div>
            </div>

        </div>
    </section><!-- #about -->

    <div class="section-counter">
        <div class="section-header wow zoomIn pb-5">
            <h3 class="section-title mb-5">Palmares</h3>
        </div>
        <div class="container wow slideInUp">
            <div class="row">
                <div class="col-sm-3 col-lg-3">
                    <div class="counter-box">
                        <div class="counter-ico">
                            <span class="ico-circle text-info"><i class="fa fa-check-double"></i></span>
                        </div>
                        <div class="counter-num">
                            <p class="counter text-info">2</p>
                            <span class="counter-text text-info">Travaux réalisés</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 col-lg-3">
                    <div class="counter-box pt-4 pt-md-0">
                        <div class="counter-ico">
                            <span class="ico-circle text-info"><i class="fa fa-calendar-alt"></i></span>
                        </div>
                        <div class="counter-num">
                            <p class="counter text-info">2</p>
                            <span class="counter-text text-info">Années d'experience</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 col-lg-3">
                    <div class="counter-box pt-4 pt-md-0">
                        <div class="counter-ico">
                            <span class="ico-circle text-info"><i class="fa fa-users"></i></span>
                        </div>
                        <div class="counter-num">
                            <p class="counter text-info">5931</p>
                            <span class="counter-text text-info">Utilisateurs & clients</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3 col-lg-3">
                    <div class="counter-box pt-4 pt-md-0">
                        <div class="counter-ico">
                            <span class="ico-circle text-info"><i class="fa fa-award"></i></span>
                        </div>
                        <div class="counter-num">
                            <p class="counter text-info">1</p>
                            <span class="counter-text text-info">Certificats obtenus</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <section class="py-3 testimony-section" id="testimony-section">
        <div class="container">
            <div class="row justify-content-center pb-3">
                <div class="col-md-7 text-center mt-3">
                    <div class="section-header wow flipInY">
                        <h3 class="section-title">Services & plateformes</h3>
                    </div>
                </div>
            </div>
            <div class="row ftco-animate justify-content-center wow slideInLeft">
                <div class="col-md-12">
                    <div class="carousel-testimony owl-carousel ftco-owl">
                        <div class="item">
                            <div class="testimony-wrap text-center py-4 pb-5">
                                <div class="user-img" style="background-image: url('<?= img_url('logos/dwa.min.png'); ?>')"></div>
                                <div class="text px-4 pb-5">
                                    <p class="name text-info">Dimtrov Web Agency</p>
                                    <span class="position">Service</span>
                                    <p class="mt-4 mb-2">
                                        DWA est un service de Dimtrov specialisé dans la conception, la realisation et le déploiement de vos sites web et
                                        applications. Nous créons des applications fluide, sur mesure et adaptées à votre image
                                    </p>
                                    <a href="<?= site_url('jumpto/dwa'); ?>" class="btn btn-outline-primary mt-3">Y accéder</a>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="testimony-wrap text-center py-4 pb-5">
                                <div class="user-img" style="background-image: url('<?= img_url('logos/dot.min.png'); ?>')"></div>
                                <div class="text px-4 pb-5">
                                    <p class="name text-info">Open Training</p>
                                    <span class="position">Plateforme</span>
                                    <p class="mt-4 mb-2">
                                        Open Training est une plateforme d'e-learning permettant aux instituts de formations de diffuser leurs programmes à
                                        une grande échelle. Par ailleurs, elle offre aux particuliers la possibilité de suivre des cours et bénéficier
                                        d'une certification.
                                    </p>
                                    <a href="<?= site_url('jumpto/dot'); ?>" class="btn btn-outline-primary mt-3">Y accéder</a>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="testimony-wrap text-center py-4 pb-5">
                                <div class="user-img" style="background-image: url('<?= img_url('logos/dam.min.png'); ?>')"></div>
                                <div class="text px-4 pb-5">
                                    <p class="name text-info">Dimtrov App Market</p>
                                    <span class="position">Service</span>
                                    <p class="mt-4 mb-2">
                                        Dimtrov App Market est l'espace de vente de nos applications de gestion. Notre équipe développe et commercialise des
                                        logicels répondant aux normes internationnalles et adaptées aux problématiques actuelles.
                                    </p>
                                    <a href="<?= site_url('jumpto/dam'); ?>" class="btn btn-outline-primary mt-3">Y accéder</a>
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="testimony-wrap text-center py-4 pb-5">
                                <div class="user-img" style="background-image: url('<?= img_url('logos/dhe.min.png'); ?>')"></div>
                                <div class="text px-4 pb-5">
                                    <p class="name text-info">House Exchange</p>
                                    <span class="position">Plateforme</span>
                                    <p class="mt-4 mb-2">
                                        La plateforme House Exchange permet aux individus de trouver des maisons ou des terrains à vendre ou à louer. Notre
                                        plateforme expose plus de 5000 proprietés afin de fournir une gammes variées à nos clients.
                                    </p>
                                    <a href="<?= site_url('jumpto/dhe'); ?>" class="btn btn-outline-primary mt-3">Y accéder</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- ##### Team Area Start ##### -->
    <section class="team-member-area my-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Section Heading -->
                    <div class="section-header mb-5 text-center wow flipInY">
                        <h3 class="section-title">Une equipe de professionnel</h3>
                    </div>
                </div>
            </div>

            <div class="row wow slideInUp">
                <!-- Single Team Member -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="single-team-member mb-5">
                        <img src="<?= img_url('staff/managers/IMG_20190313_110611.jpg'); ?>" class="w-100" alt="">
                        <!-- Hover Text -->
                        <div class="hover-text d-flex align-items-end justify-content-center text-center">
                            <div class="hover--">
                                <h4>Dimitric Sitchet</h4>
                                <h6>Founder & Chief Executive Officer</h6>
                                <div class="social-info">
                                    <a target="_blank" href="https://www.facebook.com/dimtrovich"><i class="fab fa-facebook"></i></a>
                                    <a target="_blank" href="https://twitter.com/DimitriSitchet"><i class="fab fa-twitter"></i></a>
                                    <a target="_blank" href="https://wa.me/237691889587"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
                                    <a target="_blank" href="https://www.linkedin.com/in/dimitri-sitchet-tomkeu"><i class="fab fa-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Single Team Member -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="single-team-member mb-5">
                        <img src="<?= img_url('staff/managers/FB_IMG_15573424131243243.jpg'); ?>" class="w-100" alt="">
                        <!-- Hover Text -->
                        <div class="hover-text d-flex align-items-end justify-content-center text-center">
                            <div class="hover--">
                                <h4>Annie Yanta</h4>
                                <h6>Administrative Director</h6>
                                <div class="social-info">
                                    <a href="#"><i class="fab fa-facebook"></i></a>
                                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Single Team Member -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="single-team-member mb-5">
                        <img src="<?= img_url('staff/managers/IMG-20180927-WA0011.jpg'); ?>" class="w-100" alt="">
                        <!-- Hover Text -->
                        <div class="hover-text d-flex align-items-end justify-content-center text-center">
                            <div class="hover--">
                                <h4>Nelly Nzoundja</h4>
                                <h6>Project Manager</h6>
                                <div class="social-info">
                                    <a href="#"><i class="fab fa-facebook"></i></a>
                                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Single Team Member -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="single-team-member mb-5">
                        <img src="<?= img_url('staff/managers/2b21445752006e210014f8506d6854e0.jpg'); ?>" class="w-100" alt="">
                        <!-- Hover Text -->
                        <div class="hover-text d-flex align-items-end justify-content-center text-center">
                            <div class="hover--">
                                <h4>Olivier Mpouma</h4>
                                <h6>Technical Director</h6>
                                <div class="social-info">
                                    <a href="#"><i class="fab fa-facebook"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fab fa-whatsapp"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ##### Team Area End ##### -->


</main>
