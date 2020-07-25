<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document title</title>
    <?php $this->stylesBundle('default'); ?>
</head>
<body>
    <?= $this->renderView(); ?>
    
    
    <?php $this->scriptsBundle(); ?>
</body>
</html>