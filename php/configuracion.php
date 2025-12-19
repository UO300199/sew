<!DOCTYPE HTML>

<html lang="es">
<head>
    <!-- Datos que describen el documento -->
    <meta charset="UTF-8" />
    <title>MotoGP</title>
	<meta name="author" content="Julián Fernández"/>
	<meta name="description" content="Página de configuración de la prueba de usabilidad de MotoGP Desktop"/>
	<meta name="keywords" content="moto, MotoGP"/>
	<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
	<link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
	<link rel="icon" type="image/x-icon" href="../multimedia/favicon-16px.ico">
	<link rel="icon" type="image/x-icon" href="../multimedia/favicon-32px.ico">	
	<link rel="icon" type="image/x-icon" href="../multimedia/favicon.ico">
</head>

<body>
    <header>
		<h1><a href="configuracion.php" title="Configuración test">Página de configuración de la prueba de usabilidad</a></h1>
	</header>
	<main>
    <?php
        include_once 'classConfiguracion.php';

		$configuracion =  new Configuracion();
        if (count($_POST) > 0) {
			if (isset($_POST['btCrear'])) {
				$configuracion->crearBD();
			} elseif (isset($_POST['btBorrar'])) {
				$configuracion->borrarBD();
			} elseif (isset($_POST['btReiniciar'])) {
				$configuracion->reiniciarBD();
			} elseif (isset($_POST['btExportar'])) {
				$configuracion->exportarDatos();
			} elseif (isset($_POST['btAnadir'])) {
				$configuracion->insertarPreguntas();
			}
		}
    ?>
    <form action='#' method="post">
        <button type="submit" name="btCrear" value="crear">Crear</button>
        <button type="submit" name="btBorrar" value="borrar">Borrar</button>
        <button type="submit" name="btReiniciar" value="reiniciar">Reiniciar</button>
        <button type="submit" name="btExportar" value="exportar">Exportar </button>
		<button type="submit" name="btAnadir" value="anadir">Añadir preguntas</button>
    </form>
	</main>
</body>
</html>