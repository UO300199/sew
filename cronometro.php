<!DOCTYPE HTML>

<html lang="es">
<head>
    <!-- Datos que describen el documento -->
    <meta charset="UTF-8" />
    <title>MotoGP-CronometroPHP</title>
	<meta name="author" content="Julián Fernández"/>
	<meta name="description" content="cronometro PHP de MotoGP Desktop"/>
	<meta name="keywords" content="moto, cronometro, MotoGP"/>
	<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="estilo/layout.css" />
	<link rel="icon" type="image/x-icon" href="multimedia/favicon-16px.ico">
	<link rel="icon" type="image/x-icon" href="multimedia/favicon-32px.ico">	
	<link rel="icon" type="image/x-icon" href="multimedia/favicon.ico">
</head>

<body>
    <header>
		<!-- Datos con el contenidos que aparece en el navegador -->
		<h1><a href="index.html" title="Ir a la página principal">MotoGP Desktop</a></h1>
		<nav>
			<a href="index.html" title="Página de inicio">Inicio</a>
			<a href="piloto.html" title="Información del piloto">Piloto</a>
			<a href="circuito.html" title="Información del circuito">Circuito</a>
			<a href="meteorologia.html" title="Información meteorológica">Meteorología</a>
			<a href="clasificaciones.php" title="Clasificaciones MotoGP">Clasificaciones</a>
			<a href="juegos.html" class="active" title="Juegos de MotoGP-Desktop">Juegos</a>
			<a href="ayuda.html" title="Ayuda sobre MotoGP-Desktop">Ayuda</a>
		</nav>
	</header>
    <p>Estás en: <a href="index.html" title="Página de inicio">Inicio</a> >> <a href="juegos.html" title="Juegos de MotoGP">Juegos</a> >> <strong>Cronómetro PHP</strong></p>
	<main>
    <?php
		include_once 'php/classCronometro.php';
		session_start();
		if(!isset($_SESSION['cronometro'])){
			$_SESSION['cronometro'] = new Cronometro();
		}
		$cronometro = $_SESSION['cronometro'];
		if (count($_POST) > 0) {
			if (isset($_POST['btIniciar'])) {
				$cronometro->mostrar();
				$cronometro->arrancar();
			} elseif (isset($_POST['btParar'])) {
				$cronometro->mostrar();
				$cronometro->parar();
			} elseif (isset($_POST['btMostrar'])) {
				$cronometro->mostrar();
			}
		} else {
			$cronometro->mostrar();
		}
		
	?>

        <form action='#' method="post">
            <button type="submit" name="btIniciar" value="iniciar">Iniciar</button>
            <button type="submit" name="btParar" value="parar">Parar</button>
            <button type="submit" name="btMostrar" value="mostrar">Mostrar</button>
        </form>
    </main>
</body>
</html>