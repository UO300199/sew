<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>MotoGP - Test de Usabilidad</title>
    <meta name="author" content="Julián Fernández"/>
    <meta name="description" content="Test de usabilidad de MotoGP Desktop"/>
    <meta name="keywords" content="moto, test, MotoGP"/>
    <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
    <link rel="icon" type="image/x-icon" href="../multimedia/favicon.ico">
</head>
<body>
    <h1>Test de usabilidad</h1>
    <main>
        <?php
            include_once 'classUsabilidad.php';
            session_start();
            $usabilidad = new Usabilidad();
        ?>
    </main>
</body>
</html>