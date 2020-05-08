<?php use dFramework\core\output\Layout; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $df_pageTitle; ?></title>
   
    <?php Layout::stylesBundle(); ?>
</head>
<body>
    <?php Layout::renderView(); ?>

    <?php Layout::scriptsBundle(); ?>
</body>
</html>