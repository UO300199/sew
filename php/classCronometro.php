<?php
		class Cronometro{
			private $tiempo;
			private $inicio;
			private $running;

			public function __construct(){
				$this->tiempo = 0;
			}
			public function arrancar(){
				$this->inicio = microtime(true);
				$this->running = true;
			}
			public function parar(){
				//Solo podemos parar el cronometro si no está parado
				if($this->running){
					$this->tiempo = microtime(true) - $this->inicio;
					$this->running = false;
				}
			}
			public function mostrar() {
				// minutos
				$tiempoMs = $this->tiempo * 1000;
				$minutos = intval($tiempoMs / 60000);
				$resto = $tiempoMs % 60000;

				// segundos
				$segundos = intval($resto / 1000);

				// milisegundos y décima
				$milisegundos = $resto % 1000;
				$decima = substr(strval($milisegundos), 0, 1);

				// formatear con ceros a la izquierda
				$str = str_pad($minutos, 2, "0", STR_PAD_LEFT) . ":" .
					str_pad($segundos, 2, "0", STR_PAD_LEFT) . "." .
					$decima;

				echo "<h2>Cronómetro</h2>";
				echo "<p>" .$str. "</p>";
						
			}

            /*Pensado para las pruebas de usabilidad */
            public function getTiempo() {
                return $this->tiempo;
            }
		}
	?>