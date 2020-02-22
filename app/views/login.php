<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="/lib/bootstrap/css/bootstrap-4.3.1.min.css?_ref=1554487772" />
    <title>Document</title>
</head>
<body>
    <?= $form->open(''); ?>
        <?= $form->text('login', null, ['required']); ?>
        <?= $form->password('mdp', 'Password', ['required']); ?>
        <?= $form->submit('Connexion'); ?>
    <?= $form->close(); ?>

    <p>
        <?= $error ?? null; ?>
    </p>
</body>
</html>