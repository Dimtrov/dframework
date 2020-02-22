<?php use dFramework\core\output\Layout; ?>
<?php
/**
 *  dFramework
 *
 *  The simplest PHP framework for beginners
 *  Copyright (c) 2019, Dimtrov Sarl
 *  This content is released under the Mozilla Public License 2 (MPL-2.0)
 *
 * @package        dFramework
 * @author        Dimitric Sitchet Tomkeu <dev.dimitrisitchet@gmail.com>
 * @copyright    Copyright (c) 2019, Dimtrov Sarl. (https://dimtrov.hebfree.org)
 * @copyright    Copyright (c) 2019, Dimitric Sitchet Tomkeu. (https://www.facebook.com/dimtrovich)
 * @license        https://opensource.org/licenses/MPL-2.0 MPL-2.0 License
 * @link        https://dimtrov.hebfree.org/works/dframework
 * @version 2.1
 *
 */

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
                <h1 class="mb-3 text-primary text-capitalize">Nous contacter</h1>
                <p class="breadcrumbs font-weight-bold text-black-50">
                    <span><a href="<?= site_url(); ?>">Accueil</a> <i class="fa fa-arrow-right"></i></span>
                    <span class="text-white-50">Contact</span>
                </p>
            </div>
        </div>
    </div>
</section>

<main id="main">
    <div class="container my-5">

        <div class="row flex-lg-row-reverse">
            <div class="col-lg-7">
                <form class="card" id="contactform" method="post" action="<?= site_url('home/contact'); ?>">
                    <div class="card-header">
                        <h4 class="m-0 text-center text-info">Laissez nous un message</h4>
                        <h6 class="my-2 text-center">Nous vous repondrons le plus tot possible</h6>
                    </div>
                    <div class="card-body">
                        <div class="row my-3 align-items-center">
                            <div class="col-lg-3"><label for="name">Nom</label></div>
                            <div class="col-lg-9"><input class="form-control" type="text" name="name" id="name"
                                                         placeholder="Entrez votre nom"/></div>
                        </div>
                        <div class="row my-3 align-items-center">
                            <div class="col-lg-3"><label for="email">Email</label></div>
                            <div class="col-lg-9"><input class="form-control" type="email" name="email" id="email"
                                                         placeholder="Entrez votre adresse email"/></div>
                        </div>
                        <div class="row my-3 align-items-center">
                            <div class="col-lg-3"><label for="subject">Sujet</label></div>
                            <div class="col-lg-9"><input class="form-control" type="text" name="subject" id="subject"
                                                         placeholder="Entrez le sujet de votre message"/></div>
                        </div>
                        <div class="row my-3 align-items-center">
                            <div class="col-lg-3"><label for="service">Service a contacter</label></div>
                            <div class="col-lg-9"><select name="service" id="service" class="form-control">
                                    <option value="" disabled selected>--- Selectionnez un service ---</option>
                                    <option value="sales">Service Commercial</option>
                                    <option value="technical">Service Technique</option>
                                    <option value="manager">Les managers</option>
                                </select></div>
                        </div>
                        <div class="row my-3 align-items-center">
                            <div class="col-lg-3"><label for="content">Message</label></div>
                            <div class="col-lg-9"><textarea class="form-control" name="content" id="content"
                                                            placeholder="Entrez votre message ici"
                                                            style="height:10em"></textarea></div>
                        </div>
                        <div class="row my-3 flex-column justify-content-center align-items-center">
                            <div class="result"></div>
                            <input type="text" name="anythings" />
                            <button type="submit" class="btn btn-primary">Envoyer</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-5">
                <div class="row">
                    <div class="col-lg-12 mb-2">
                        <div class="d-flex">
                            <span class="fa fa-stack fa-1x text-primary">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-home fa-stack-1x fa-inverse"></i>
                            </span>
                            <div class="ml-2">
                                <h6 class="m-0">Yaoundé, Cameroun</h6>
                                <p class="small text-muted mt-2">Awae Escallier</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <span class="fa fa-stack fa-1x text-primary">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-phone fa-stack-1x fa-inverse"></i>
                            </span>
                            <div class="ml-2">
                                <h6 class="m-0">(+237) 691 88 95 87 / 673 40 66 61</h6>
                                <p class="small text-muted mt-2">Lun - Ven | 10h - 16h</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <span class="fa fa-stack fa-1x text-primary">
                                <i class="fa fa-circle fa-stack-2x"></i>
                                <i class="fa fa-envelope fa-stack-1x fa-inverse"></i>
                            </span>
                            <div class="ml-2">
                                <h6 class="m-0">dimtrov@hebfree.org | groupedimtrov@gmail.com</h6>
                                <p class="small text-muted mt-2">Envoyez nous vos preoccupations et nous trouverons des solutions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>


<?php Layout::block('js'); ?>
$(document).ready(function(){
    $('#contactform').submit(function(e){
        e.preventDefault();
        var form = $(this),
            btn = form.find('button'),
            result = form.find('div.result');
        result.fadeOut(500);
        btn.html(btn.text() + ' &nbsp; <i class="fa fa-circle-notch fa-spin"></i>');
        setTimeout(function () {
            $.ajax({
                type:'POST', url:form.attr('action'), data: form.serialize(),
                error: function (a, b, c) {
                    result.html('<div class="alert alert-danger text-center">Une erreur s\'est produite ['+a+']</div>');
                },
                success: function (data) {
                    var class_name = 'success';
                    if(/<error>/.test(data)) {
                        class_name = 'danger';
                    }
                    if(/<ok>/.test(data)) {
                        setTimeout(function(){
                            window.location.reload();
                        }, 1500);
                    }
                    result.html('<div class="alert alert-'+class_name+' text-center">'+data.replace('<error>','').replace('<ok>','')+'</div>');
                }
            });
            result.fadeIn(600, function(){
                btn.html(btn.text().trim());
            });
        }, 1500);
    });
    $('input[name="anythings"]').hide();
});
<?php Layout::end(); ?>
