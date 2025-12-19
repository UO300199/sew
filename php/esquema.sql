-- Tabla Usuarios
CREATE TABLE Usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,

    profesion TEXT NOT NULL,
    edad INT NOT NULL,
    CHECK (edad > 0),

    genero ENUM('masculino', 'femenino', 'otro') NOT NULL,

    pericia_informatica INT NOT NULL,
    CHECK (pericia_informatica >= 0 AND pericia_informatica <= 10)
);


-- Tabla Tests de Usabilidad
CREATE TABLE TestsUsabilidad (
    id_test INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,

    dispositivo ENUM('ordenador', 'tablet', 'telefono') NOT NULL,
    tiempo_segundos INT NOT NULL,

    completado BOOLEAN NOT NULL,

    comentarios TEXT NOT NULL,
    propuestas_mejora TEXT,

    valoracion INT NULL,
    CHECK (valoracion >= 0 AND valoracion <= 10),

    CONSTRAINT fk_test_usuario 
        FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario)
);


-- Tabla Preguntas
CREATE TABLE Preguntas(
    id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    enunciado TEXT NOT NULL
);

-- Tabla Respuestas del usuario
CREATE TABLE Respuestas (
    id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_test INT NOT NULL,
    id_pregunta INT NOT NULL,
    respuesta TEXT NOT NULL,

    CONSTRAINT fk_resp_test 
        FOREIGN KEY (id_test) REFERENCES TestsUsabilidad(id_test),

    CONSTRAINT fk_resp_pregunta 
        FOREIGN KEY (id_pregunta) REFERENCES Preguntas(id_pregunta)
);

-- Tabla Observaciones del Facilitador
CREATE TABLE ObservacionesFacilitador (
    id_observacion INT AUTO_INCREMENT PRIMARY KEY,
    id_test INT NOT NULL,
    comentarios TEXT NOT NULL,

    CONSTRAINT fk_obs_test 
        FOREIGN KEY (id_test) REFERENCES TestsUsabilidad(id_test)
);
