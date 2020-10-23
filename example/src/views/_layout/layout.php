<?php /** @var string $title */ ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>My test page</title>
    <link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
    <link href="/public/css/style.css" rel="stylesheet" type="text/css">
    <title><?=$title?></title>
</head>
<body>

<jet-container name="content"></jet-container>

<jet-include path="_layout/footer.php"></jet-include>

</body>
</html>