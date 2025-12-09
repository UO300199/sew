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
            die("❌ Error: No se puede conectar al servidor MySQL. Verifica que XAMPP esté ejecutándose y las credenciales sean correctas.<br>Error: " . $conn->connect_error);
        }
        
        $conn->close();
    }

    // Método para conectar al servidor MySQL (sin especificar BD)
    private function conectarServidor() {
        $conn = new mysqli($this->servername, $this->username, $this->password);

        if ($conn->connect_error) {
            die("❌ Conexión fallida al servidor: " . $conn->connect_error);
        }
        
        return $conn;
    }

    // Método para conectar a la base de datos específica
    private function conectarBD() {
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        if ($conn->connect_error) {
            die("❌ Conexión fallida a la base de datos: " . $conn->connect_error);
        }
        
        return $conn;
    }

    // Método 2: Crear la base de datos y tablas
    public function crearBD() {
        $conn = $this->conectarServidor();

        // Crear la base de datos
        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->dbname . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if ($conn->query($sql) === TRUE) {
            echo "✅ Base de datos '{$this->dbname}' creada correctamente<br>";
        } else {
            echo "❌ Error al crear la base de datos: " . $conn->error . "<br>";
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
                echo "✅ Script SQL ejecutado correctamente<br>";
                
                // Limpiar resultados pendientes
                while ($conn->next_result()) {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                }
            } else {
                echo "❌ Error al ejecutar el script SQL: " . $conn->error . "<br>";
            }
        } else {
            // Si no existe el archivo, crear las tablas directamente
            echo "⚠️ Archivo esquema.sql no encontrado, creando tablas desde el código...<br>";
        }

        $conn->close();
        echo "<strong>🎉 Proceso completado</strong><br>";
    }

    
    // Método 3: Borrar la base de datos
    public function borrarBD() {
        $conn = $this->conectarServidor();

        $sql = "DROP DATABASE IF EXISTS " . $this->dbname;
        if ($conn->query($sql) === TRUE) {
            echo "🗑️ Base de datos '{$this->dbname}' eliminada correctamente<br>";
        } else {
            echo "❌ Error al eliminar la base de datos: " . $conn->error . "<br>";
        }

        $conn->close();
    }

    // Método 4: Reiniciar datos de las tablas
    public function reiniciarBD() {
        $conn = $this->conectarBD();

        // Desactivar verificación de claves foráneas temporalmente
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // Obtener todas las tablas de la base de datos
        $result = $conn->query("SHOW TABLES");
        
        if ($result) {
            while ($row = $result->fetch_array()) {
                $table = $row[0];
                if ($conn->query("TRUNCATE TABLE $table")) {
                    echo "✅ Datos de la tabla '$table' eliminados correctamente<br>";
                } else {
                    echo "❌ Error eliminando datos de '$table': " . $conn->error . "<br>";
                }
            }
        } else {
            echo "❌ Error al obtener las tablas: " . $conn->error . "<br>";
        }

        // Reactivar verificación de claves foráneas
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        $conn->close();
        echo "<strong>🎉 Datos reiniciados</strong><br>";
    }

    // Método 5: Exportar datos
    public function exportarDatos() {
        $conn = $this->conectarBD();

        // Obtener todas las tablas
        $result = $conn->query("SHOW TABLES");
        $tablasExportadas = 0;

        if ($result) {
            while ($row = $result->fetch_array()) {
                $tabla = $row[0];
                $filename = strtolower($tabla) . "_data.csv";
                
                if ($this->exportarTabla($conn, $tabla, $filename)) {
                    $tablasExportadas++;
                }
            }
        }

        $conn->close();
        
        if ($tablasExportadas > 0) {
            echo "<strong>🎉 Exportación completada: $tablasExportadas tabla(s)</strong><br>";
        } else {
            echo "⚠️ No hay datos para exportar<br>";
        }
    }

    // Método auxiliar para exportar una tabla específica
    private function exportarTabla($conn, $tabla, $filename) {
        $sql = "SELECT * FROM $tabla";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $file = fopen($filename, "w");

            // Escribir encabezados
            $headers = array();
            $fields = $result->fetch_fields();
            foreach ($fields as $field) {
                $headers[] = $field->name;
            }
            fputcsv($file, $headers);

            // Escribir datos
            while ($row = $result->fetch_assoc()) {
                fputcsv($file, $row);
            }

            fclose($file);
            echo "✅ Datos de '$tabla' exportados a '$filename' ({$result->num_rows} registro(s))<br>";
            return true;
        } else {
            echo "⚠️ No hay datos en la tabla '$tabla'<br>";
            return false;
        }
    }

    // Método adicional: Verificar estado de la BD
    public function verificarEstado() {
        $conn = $this->conectarServidor();
        
        // Verificar si existe la base de datos
        $result = $conn->query("SHOW DATABASES LIKE '{$this->dbname}'");
        
        if ($result->num_rows > 0) {
            echo "✅ La base de datos '{$this->dbname}' existe<br>";
            
            $conn->close();
            $conn = $this->conectarBD();
            
            // Mostrar las tablas
            $result = $conn->query("SHOW TABLES");
            if ($result->num_rows > 0) {
                echo "📊 Tablas encontradas:<br>";
                while ($row = $result->fetch_array()) {
                    $tabla = $row[0];
                    $countResult = $conn->query("SELECT COUNT(*) as total FROM $tabla");
                    $count = $countResult->fetch_assoc()['total'];
                    echo "&nbsp;&nbsp;&nbsp;• $tabla ($count registro(s))<br>";
                }
            } else {
                echo "⚠️ No hay tablas en la base de datos<br>";
            }
        } else {
            echo "❌ La base de datos '{$this->dbname}' NO existe<br>";
        }
        
        $conn->close();
    }
}
?>