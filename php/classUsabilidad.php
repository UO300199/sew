<?php
include_once 'classConfiguracion.php';
include_once 'classCronometro.php';

class Usabilidad {
    private $ventana;
    private $configuracion;
    private $cronometroUsabilidad;
    private $noContestadas = 0;
    private $errores = [];
    
    public function __construct() {
        $this->configuracion = new Configuracion();
        
        if (!isset($_SESSION['ventana'])) {
            $_SESSION['ventana'] = 0;
        }
        if (!isset($_SESSION['id_usuario'])) {
            $_SESSION['id_usuario'] = null;
        }
        if (!isset($_SESSION['id_test'])) {
            $_SESSION['id_test'] = null;
        }
        if(!isset($_SESSION['cronometroUsabilidad'])){
            $_SESSION['cronometroUsabilidad'] = new Cronometro();
        }
        $this->cronometroUsabilidad = $_SESSION['cronometroUsabilidad'];
        
        $this->ventana = $_SESSION['ventana'];
        $this->procesarFormulario();
        $this->show();
    }
    
    private function procesarFormulario() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return;
        }
        
        if (isset($_POST['btIniciar']) && $this->ventana == 0) {
            if ($this->validarDatosUsuario($_POST)) {
                if ($this->guardarUsuarioYTest($_POST)) {
                    // Arrancar el cronómetro al iniciar la prueba
                    $this->cronometroUsabilidad->arrancar();
                    $_SESSION['cronometroUsabilidad'] = $this->cronometroUsabilidad;
                    $_SESSION['ventana'] = $this->ventana = 1;
                }
            }
        } elseif (isset($_POST['btTerminar']) && $this->ventana == 1) {
            if ($this->validarRespuestas($_POST)) {
                // Parar el cronómetro al terminar las preguntas
                $this->cronometroUsabilidad->parar();
                $_SESSION['cronometroUsabilidad'] = $this->cronometroUsabilidad;
                
                if ($this->guardarRespuestas($_POST)) {
                    $_SESSION['ventana'] = $this->ventana = 2;
                }
            }
        } elseif (isset($_POST['btValoracion']) && $this->ventana == 2) {
            if ($this->validarValoracion($_POST)) {
                if ($this->guardarValoracion($_POST)) {
                    $_SESSION['ventana'] = $this->ventana = 3;
                }
            }
        } elseif (isset($_POST['btObservaciones']) && $this->ventana == 3) {
            if ($this->validarObservaciones($_POST)) {
                if ($this->guardarObservaciones($_POST)) {
                    $_SESSION['ventana'] = $this->ventana = 4;
                }
            }
        } elseif (isset($_POST['btNuevaPrueba']) && $this->ventana == 4) {
            $this->reiniciarPrueba();
        }
    }
    
    // VALIDACIONES - Lógica de negocio separada
    
    private function validarDatosUsuario($datos) {
        $this->errores = [];
        
        if (!isset($datos['edad']) || !is_numeric($datos['edad']) || $datos['edad'] < 1) {
            $this->errores[] = "La edad debe ser un número positivo";
        }
        
        if (!isset($datos['genero']) || !in_array($datos['genero'], ['masculino', 'femenino', 'otro'])) {
            $this->errores[] = "Debe seleccionar un género válido";
        }
        
        if (!isset($datos['profesion']) || trim($datos['profesion']) === '') {
            $this->errores[] = "La profesión es obligatoria";
        }
        
        if (!isset($datos['pericia']) || !is_numeric($datos['pericia']) || $datos['pericia'] < 0 || $datos['pericia'] > 10) {
            $this->errores[] = "La pericia informática debe ser un valor entre 0 y 10";
        }
        
        if (!isset($datos['disOption']) || !in_array($datos['disOption'], ['ordenador', 'tablet', 'telefono'])) {
            $this->errores[] = "Debe seleccionar un dispositivo válido";
        }
        
        return empty($this->errores);
    }
    
    private function validarRespuestas($datos) {
        $this->errores = [];
        $conn = $this->configuracion->conectarBD();
        $stmt = $conn->prepare("SELECT id_pregunta FROM Preguntas");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $id = $row['id_pregunta'];
            $campo = "pregunta_$id";
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
                $this->errores[] = "Debe responder la pregunta $id";
            }
        }
        
        $stmt->close();
        $conn->close();
        
        return empty($this->errores);
    }
    
    private function validarValoracion($datos) {
        $this->errores = [];
        
        if (!isset($datos['comentarios']) || trim($datos['comentarios']) === '') {
            $this->errores[] = "Los comentarios son obligatorios";
        }
        
        if (!isset($datos['propuestas']) || trim($datos['propuestas']) === '') {
            $this->errores[] = "Las propuestas de mejora son obligatorias";
        }
        
        if (!isset($datos['valoracion']) || !is_numeric($datos['valoracion']) || $datos['valoracion'] < 0 || $datos['valoracion'] > 10) {
            $this->errores[] = "La valoración debe ser un número entre 0 y 10";
        }
        
        return empty($this->errores);
    }
    
    private function validarObservaciones($datos) {
        $this->errores = [];
        
        if (!isset($datos['observaciones']) || trim($datos['observaciones']) === '') {
            $this->errores[] = "Las observaciones son obligatorias";
        }
        
        return empty($this->errores);
    }
    
    // OPERACIONES DE BASE DE DATOS - Con prepared statements
    
    private function guardarUsuarioYTest($datos) {
        $conn = $this->configuracion->conectarBD();
        
        try {
            $conn->begin_transaction();
            
            // Insertar usuario con prepared statement
            $stmt = $conn->prepare("INSERT INTO Usuarios (profesion, edad, genero, pericia_informatica) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sisi", $datos['profesion'], $datos['edad'], $datos['genero'], $datos['pericia']);
            $stmt->execute();
            
            $id_usuario = $conn->insert_id;
            $_SESSION['id_usuario'] = $id_usuario;
            $stmt->close();
            
            // Insertar test inicial
            $stmt = $conn->prepare("INSERT INTO TestsUsabilidad (id_usuario, dispositivo, tiempo_segundos, completado, comentarios) VALUES (?, ?, 0, 0, '')");
            $stmt->bind_param("is", $id_usuario, $datos['disOption']);
            $stmt->execute();
            
            $id_test = $conn->insert_id;
            $_SESSION['id_test'] = $id_test;
            $stmt->close();
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            $this->errores[] = "Error al guardar los datos: " . $e->getMessage();
            return false;
        } finally {
            $conn->close();
        }
    }
    
    private function guardarRespuestas($datos) {
        $conn = $this->configuracion->conectarBD();
        $id_test = $_SESSION['id_test'];
        
        try {
            $conn->begin_transaction();
            
            // Actualizar tiempo del test
            $tiempo_segundos = intval($this->cronometroUsabilidad->getTiempo());
            $stmt = $conn->prepare("UPDATE TestsUsabilidad SET tiempo_segundos = ? WHERE id_test = ?");
            $stmt->bind_param("ii", $tiempo_segundos, $id_test);
            $stmt->execute();
            $stmt->close();
            
            // Guardar respuestas
            $stmt = $conn->prepare("INSERT INTO Respuestas (id_test, id_pregunta, respuesta) VALUES (?, ?, ?)");
            
            foreach ($datos as $key => $value) {
                if (strpos($key, 'pregunta_') === 0) {
                    $id_pregunta = str_replace('pregunta_', '', $key);
                    //Si ha contesado con [nN][cC] guardamos noCompletado
                    if (preg_match('/^[nN][cC]$/', trim($value))) {
                        $this->noContestadas +=1;
                    }
                    $stmt->bind_param("iis", $id_test, $id_pregunta, $value);
                    $stmt->execute();
                }
            }
            
            $stmt->close();
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            $this->errores[] = "Error al guardar las respuestas: " . $e->getMessage();
            return false;
        } finally {
            $conn->close();
        }
    }
    
    private function guardarValoracion($datos) {
        $conn = $this->configuracion->conectarBD();
        $id_test = $_SESSION['id_test'];
        
        try {
            $stmt = $conn->prepare("UPDATE TestsUsabilidad SET comentarios = ?, propuestas_mejora = ?, valoracion = ? WHERE id_test = ?");
            $stmt->bind_param("ssii", $datos['comentarios'], $datos['propuestas'], $datos['valoracion'], $id_test);
            $stmt->execute();
            $stmt->close();
            
            return true;
            
        } catch (Exception $e) {
            $this->errores[] = "Error al guardar la valoración: " . $e->getMessage();
            return false;
        } finally {
            $conn->close();
        }
    }
    
    private function guardarObservaciones($datos) {
        $conn = $this->configuracion->conectarBD();
        $id_test = $_SESSION['id_test'];
        
        try {
            $conn->begin_transaction();
            
            // Contar respuestas "NC" desde la BD
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM Respuestas WHERE id_test = ? AND UPPER(TRIM(respuesta)) = 'NC'");
            $stmt->bind_param("i", $id_test);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $noContestadas = $row['total'];
            $stmt->close();
            
            // Guardar observaciones...
            $stmt = $conn->prepare("INSERT INTO ObservacionesFacilitador (id_test, comentarios) VALUES (?, ?)");
            $stmt->bind_param("is", $id_test, $datos['observaciones']);
            $stmt->execute();
            $stmt->close();
            
            // Marcar como completado
            $completado = ($noContestadas == 0) ? 1 : 0;
            $stmt = $conn->prepare("UPDATE TestsUsabilidad SET completado = ? WHERE id_test = ?");
            $stmt->bind_param("ii", $completado, $id_test);
            $stmt->execute();
            $stmt->close();
                
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            $this->errores[] = "Error al guardar las observaciones: " . $e->getMessage();
            return false;
        } finally {
            $conn->close();
        }
    }
    
    // PRESENTACIÓN - Métodos de visualización separados
    
    private function show() {
        // Mostrar errores si existen
        if (!empty($this->errores)) {
            $this->mostrarErrores($this->errores);
        }
        
        switch ($this->ventana) {
            case 0: $this->mostrarPantallaInicio(); break;
            case 1: $this->mostrarPantallaPreguntas(); break;
            case 2: $this->mostrarPantallaValoracion(); break;
            case 3: $this->mostrarPantallaObservaciones(); break;
            case 4: $this->mostrarPantallaFinal(); break;
            default: echo "<p>Estado inválido de la aplicación</p>";
        }
    }
    
    private function mostrarErrores($errores) {
        echo "<section>";
        echo "<h3>Errores encontrados:</h3>";
        echo "<ul>";
        foreach ($errores as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
        echo "</section>";
    }
    
    private function mostrarPantallaInicio() {
        echo "<h2>Introduzca los datos</h2>";
        echo "<form method='post' action='#'>";
        
        echo "<p>";
        echo "<label for='edad'>Edad:</label>";
        echo "<input type='number' id='edad' name='edad' min='1' required >";
        echo "</p>";
        
        echo "<fieldset>";
        echo "<legend>Género:</legend>";
        echo "<p>";
        echo "<label for='masculino'>Masculino</label>";
        echo "<input type='radio' id='masculino' name='genero' value='masculino' required >";

        echo "<label for='femenino'>Femenino</label>";
        echo "<input type='radio' id='femenino' name='genero' value='femenino'>";

        echo "<label for='otro'>Otro</label>";
        echo "<input type='radio' id='otro' name='genero' value='otro'>";
        echo "</p>";
        echo "</fieldset>";
        
        echo "<p>";
        echo "<label for='profesion'>Profesión:</label>";
        echo "<input type='text' id='profesion' name='profesion' required >";
        echo "</p>";
        
        echo "<p>";
        echo "<label for='pericia'>Pericia informática (0-10):</label>";
        echo "<input type='number' id='pericia' name='pericia' min='0' max='10' required >";
        echo "</p>";
        
        echo "<p>";
        echo "<label for='disOption'>Dispositivo:</label>";
        echo "<select id='disOption' name='disOption' required>";
        echo "<option value='' disabled selected>-- Seleccione un dispositivo --</option>";
        echo "<option value='ordenador'>Ordenador</option>";
        echo "<option value='tablet'>Tablet</option>";
        echo "<option value='telefono'>Móvil</option>";
        echo "</select>";
        echo "</p>";
        
        echo "<p>";
        echo "<button type='submit' name='btIniciar'>Iniciar prueba</button>";
        echo "</p>";
        echo "</form>";
    }
    
    private function mostrarPantallaPreguntas() {
        $conn = $this->configuracion->conectarBD();
        $stmt = $conn->prepare("SELECT id_pregunta, enunciado FROM Preguntas ORDER BY id_pregunta");
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "<h2>Preguntas de MotoGPDesktop</h2>";
        echo "<p>Se deben contestar todas las respuestas</p>";
        echo "<p>Si no se conoce la respuesta escribir NS</p>";
        
        
        if ($result && $result->num_rows > 0) {
            echo "<form method='post' action='#'>";
            while ($row = $result->fetch_assoc()) {
                $id = $row['id_pregunta'];
                $enunciado = htmlspecialchars($row['enunciado']);
                echo "<p>";
                echo "<label for='pregunta_$id'>$enunciado</label>";
                echo "<input type='text' id='pregunta_$id' name='pregunta_$id' required >";
                echo "</p>";
            }
            echo "<p>";
            echo "<button type='submit' name='btTerminar'>Terminar preguntas</button>";
            echo "</p>";
            echo "</form>";
        } else {
            echo "<p>No hay preguntas disponibles</p>";
        }
        
        $stmt->close();
        $conn->close();
    }
    
    private function mostrarPantallaValoracion() {
        echo "<h2>Añada comentarios de valoración</h2>";
        
        
        echo "<form method='post' action='#'>";
        
        echo "<p>";
        echo "<label for='comentarios'>Comentarios:</label>";
        echo "<textarea id='comentarios' name='comentarios' rows='4' cols='50' required ></textarea>";
        echo "</p>";
        
        echo "<p>";
        echo "<label for='propuestas'>Propuestas de mejora:</label>";
        echo "<textarea id='propuestas' name='propuestas' rows='4' cols='50' required ></textarea>";
        echo "</p>";
        
        echo "<p>";
        echo "<label for='valoracion'>Valoración (0-10):</label>";
        echo "<input type='number' id='valoracion' name='valoracion' min='0' max='10' required >";
        echo "</p>";
        
        echo "<p>";
        echo "<button type='submit' name='btValoracion'>Añadir valoración</button>";
        echo "</p>";
        echo "</form>";
    }
    
    private function mostrarPantallaObservaciones() {
        echo "<h2>Gracias por completar la prueba</h2>";
        echo "<p>Añada las observaciones del facilitador</p>";
        echo "<form method='post' action='#'>";
        
        echo "<p>";
        echo "<label for='observaciones'>Observaciones:</label>";
        echo "<textarea id='observaciones' name='observaciones' rows='6' cols='50' required ></textarea>";
        echo "</p>";
        
        echo "<p>";
        echo "<button type='submit' name='btObservaciones'>Añadir observaciones</button>";
        echo "</p>";
        echo "</form>";
    }
    
    private function mostrarPantallaFinal() {
        echo "<h2>Prueba finalizada</h2>";
        echo "<p>La prueba se ha guardado correctamente</p>";
        
        echo "<form method='post' action='#'>";
        echo "<p>";
        echo "<button type='submit' name='btNuevaPrueba'>Nueva prueba</button>";
        echo "</p>";
        echo "</form>";
    }
    
    private function reiniciarPrueba() {
        session_destroy();
        session_start();
        $_SESSION['ventana'] = 0;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>