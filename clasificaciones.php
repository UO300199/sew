<!DOCTYPE HTML>

<html lang="es">
<head>
    <!-- Datos que describen el documento -->
    <meta charset="UTF-8" />
    <title>MotoGP-Clasificaciones</title>
	<meta name="author" content="Julián Fernández"/>
	<meta name="description" content="Clasificaciones de MotoGP"/>
	<meta name="keywords" content="moto, clasificaciones, MotoGP"/>
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
			<a href="clasificaciones.php" class="active" title="Clasificaciones MotoGP">Clasificaciones</a>
			<a href="juegos.html" title="Juegos de MotoGP-Desktop">Juegos</a>
			<a href="ayuda.html" title="Ayuda sobre MotoGP-Desktop">Ayuda</a>
		</nav>
	</header>
	<p>Estás en: <a href="index.html" title="Página de inicio">Inicio</a> >> <strong>Clasificaciones</strong></p>
	<main>
	<h2>Clasificaciones de MotoGP</h2>
	<?php
		class Clasificacion {
			private $documento;

			public function __construct() {
				$this->documento = "xml/circuitoEsquema.xml";
			}

			public function consultar() {
				$datos = file_get_contents($this->documento);
				if ($datos == null){
					echo "<p>Error al leer el documento XML.</p>";
				} else {
					// Se convierte el string en un objeto XML
					$xml = new SimpleXMLElement($datos);
					$this->mostrarGanador($xml);
					$this->mostrarClasificacion($xml);
				}
			}

			private function mostrarGanador($xml) {
				echo "<h3>Ganador de la carrera</h3>";
				echo "<p>Vencedor: {$xml->resultado_carrera->vencedor->nombre}</p>";
				echo "<p>Equipo: {$xml->resultado_carrera->vencedor->equipo}</p>";
				echo "<p>Tiempo: {$xml->resultado_carrera->vencedor->tiempo->horas}h {$xml->resultado_carrera->vencedor->tiempo->minutos}m {$xml->resultado_carrera->vencedor->tiempo->segundos}s {$xml->resultado_carrera->vencedor->tiempo->milisegundos}ms</p>";
			}

			private function mostrarClasificacion($xml) {
				echo "<h3>Clasificación tras la carrera</h3>";
				echo "<ul>";
				foreach ($xml->clasificacion_mundial_2025->piloto as $piloto) {
					echo "<li>";
					echo "Posición: {$piloto['posicion']}, ";
					echo "Nombre: {$piloto->nombre}, ";
					echo "Equipo: {$piloto->equipo}, ";
					echo "Puntos: {$piloto->puntos}";
					echo "</li>";
				}
				echo "</ul>";
			}
		}

		$clasificacion = new Clasificacion();
		$clasificacion->consultar();
	?>
	</main>
</body>
</html>