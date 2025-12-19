<?php
class Configuracion {
    private $servername;
    private $username;
    private $password;
    private $dbname;
    public $conn;

    public function __construct() {
        $this->servername = "localhost";
        $this->username = "DBUSER2025";
        $this->password = "DBPSWD2025";
        $this->dbname = "UO300199_DB";

        // Verificar conexión al servidor (no a la BD)
        $this->verificarConexionServidor();
    }

    // Verificar que podemos conectar al servidor MySQL
    private function verificarConexionServidor() {
        $conn = @new mysqli($this->servername, $this->username, $this->password);
        
        if ($conn->connect_error) {
            echo "<p>Error: No se puede conectar al servidor MySQL. " . $conn->connect_error . "</p>";
        }
        
        $conn->close();
    }

    // Método para conectar al servidor MySQL (sin especificar BD)
    private function conectarServidor() {
        $conn = new mysqli($this->servername, $this->username, $this->password);

        if ($conn->connect_error) {
            $conn->close();
            die("<p>Error: No se puede conectar al servidor MySQL. " . $conn->connect_error . "</p>");
        }
        
        return $conn;
    }

    // Método para conectar a la base de datos específica
    public function conectarBD() {
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        if ($conn->connect_error) {
            $conn->close();
            die("<p>Conexión fallida a la base de datos: " . $conn->connect_error . "</p>");
        }
        
        return $conn;
    }

    // Método 2: Crear la base de datos y tablas
    public function crearBD() {
        $conn = $this->conectarServidor();

        // Crear la base de datos
        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->dbname . " COLLATE utf8_spanish_ci";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Base de datos '{$this->dbname}' creada correctamente</p>";
        } else {
            echo "<p>Error al crear la base de datos: " . $conn->error . "</p>";
            $conn->close();
            return;
        }

        $conn->close();

        // Ahora conectar a la base de datos y crear las tablas
        $conn = $this->conectarBD();

        // Leer el fichero SQL si existe
        $sqlFile =  "esquema.sql";
        
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            
            if ($conn->multi_query($sql)) {
                echo "<p>Script SQL ejecutado correctamente</p>";
                
            } else {
                echo "<p>Error al ejecutar el script SQL: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Archivo esquema.sql no encontrado.</p>";
        }

        $conn->close();
        echo "<p>Proceso completado</p>";
    }

    // Método 3: Borrar la base de datos
    public function borrarBD() {
        $conn = $this->conectarServidor();

        $sql = "DROP DATABASE IF EXISTS " . $this->dbname;
        if ($conn->query($sql) === TRUE) {
            echo "<p>Base de datos '{$this->dbname}' eliminada correctamente</p>";
        } else {
            echo "<p>Error al eliminar la base de datos: " . $conn->error . "</p>";
        }

        $conn->close();
    }

    // Método 4: Reiniciar datos de las tablas
    public function reiniciarBD() {
        $conn = $this->conectarBD();

        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        $result = $conn->query("SHOW TABLES");
        
        if ($result) {
            while ($row = $result->fetch_array()) {
                $table = $row[0];
                if ($conn->query("TRUNCATE TABLE $table")) {
                    echo "<p>Datos de la tabla '$table' eliminados correctamente</p>";
                } else {
                    echo "<p>Error eliminando datos de '$table': " . $conn->error . "</p>";
                }
            }
        } else {
            echo "<p>Error al obtener las tablas: " . $conn->error . "</p>";
        }

        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        $conn->close();
        echo "<p>Datos reiniciados</p>";
    }

    // Método 5: Exportar datos
    public function exportarDatos() {
        $conn = $this->conectarBD();

        $sql = "
            SELECT 
                u.id_usuario,
                u.profesion,
                u.edad,
                u.genero,
                u.pericia_informatica,

                t.id_test,
                t.dispositivo,
                t.tiempo_segundos,
                CASE WHEN t.completado=1 THEN 'Si' ELSE 'No' END AS completado,
                t.comentarios,
                t.propuestas_mejora,
                t.valoracion,

                o.id_observacion,
                o.comentarios AS observaciones_facilitador,

                MAX(CASE WHEN r.id_pregunta = 1  THEN r.respuesta END) AS p_1,
                MAX(CASE WHEN r.id_pregunta = 2  THEN r.respuesta END) AS p_2,
                MAX(CASE WHEN r.id_pregunta = 3  THEN r.respuesta END) AS p_3,
                MAX(CASE WHEN r.id_pregunta = 4  THEN r.respuesta END) AS p_4,
                MAX(CASE WHEN r.id_pregunta = 5  THEN r.respuesta END) AS p_5,
                MAX(CASE WHEN r.id_pregunta = 6  THEN r.respuesta END) AS p_6,
                MAX(CASE WHEN r.id_pregunta = 7  THEN r.respuesta END) AS p_7,
                MAX(CASE WHEN r.id_pregunta = 8  THEN r.respuesta END) AS p_8,
                MAX(CASE WHEN r.id_pregunta = 9  THEN r.respuesta END) AS p_9,
                MAX(CASE WHEN r.id_pregunta = 10 THEN r.respuesta END) AS p_10
            FROM Usuarios u
            JOIN TestsUsabilidad t
                ON u.id_usuario = t.id_usuario
            LEFT JOIN ObservacionesFacilitador o
                ON t.id_test = o.id_test
            LEFT JOIN Respuestas r
                ON t.id_test = r.id_test
            GROUP BY
                u.id_usuario,
                u.profesion,
                u.edad,
                u.genero,
                u.pericia_informatica,
                t.id_test,
                t.dispositivo,
                t.tiempo_segundos,
                t.completado,
                t.comentarios,
                t.propuestas_mejora,
                t.valoracion,
                o.comentarios,
                o.id_observacion
            ORDER BY u.id_usuario, t.id_test
        ";

        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $filename = "export_usuarios_tests.csv";
            $file = fopen($filename, "w");

            // Escribir cabeceras
            $fields = $result->fetch_fields();
            $headers = [];
            foreach ($fields as $field) {
                $headers[] = $field->name;
            }
            fputcsv($file, $headers);

            // Escribir filas
            while ($row = $result->fetch_assoc()) {
                // Limpiar saltos de línea de todos los campos de texto
                foreach ($row as $key => $value) {
                    if (is_string($value)) {
                        // Reemplazar saltos de línea por espacios
                        $row[$key] = str_replace(["\r\n", "\r", "\n"], ' ', $value);
                        // Eliminar espacios al inicio y final
                        $row[$key] = trim($row[$key]);
                    }
                }
                fputcsv($file, $row);
            }

            fclose($file);
            echo "<p>Datos exportados a '$filename' ({$result->num_rows} registro(s))</p>";
        } else {
            echo "<p>No hay datos para exportar con la consulta proporcionada</p>";
        }

        $conn->close();
    }

    // Insertar datos de prueba
    public function insertarPreguntas() {
        $conn = $this->conectarBD();

        $sqlFile =  "preguntas.sql";
        
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            
            if ($conn->multi_query($sql)) {
                echo "<p>Preguntas añadidas correctamente</p>";
                
            } else {
                echo "<p>Error al ejecutar el script SQL: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Archivo preguntas.sql no encontrado.</p>";
        }

        $conn->close();
    }

}
?>
