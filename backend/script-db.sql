-- --------------------------------------------------------
-- PROYECTO: Sistema Socioeconómico IUJO BARQUISIMETO
-- FECHA: 2026
-- PHP VERSION COMPATIBLE: 7.1.26
-- --------------------------------------------------------

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. CREACIÓN DE LA BASE DE DATOS
CREATE DATABASE IF NOT EXISTS socioeconomico_db CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE socioeconomico_db;

-- 2. BLOQUE DE TABLAS CATÁLOGO (Maestras)
-- Estas tablas no dependen de nadie y deben existir primero.
CREATE TABLE Instituto (
		id INT AUTO_INCREMENT PRIMARY KEY,
		siglas VARCHAR(20) NOT NULL UNIQUE,
		nombre VARCHAR(100) NOT NULL UNIQUE,
		activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE Nacionalidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE Sexo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE TipoEstudiante (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE Carrera (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE Semestre (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    numero INT NOT NULL,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE EstadoCivil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE CondicionLaboral (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE RelacionLaboral (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE TipoOrganizacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE SectorTrabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE CategoriaOcupacional (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE TipoConvivencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE TipoVivienda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100), -- Cambiado a VARCHAR para ser descriptivo
    valor_estrato INT,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE TenenciaVivienda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE AmbienteVivienda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE ActivoVivienda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE ServicioVivienda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE FrecuenciaServicioAgua (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE FrecuenciaServicioAseo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE FrecuenciaServicioElectricidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE FrecuenciaServicioGas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE Transporte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE DependenciaEconomica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE FuenteIngresoFamiliar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    valor_estrato INT,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IngresoFamiliar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE NivelEducacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    valor_estrato INT,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE TipoEmpresa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE Veracidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE TipoBeca (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instituto_id INT NOT NULL, -- Ahora la beca "pertenece" a una sede
    nombre VARCHAR(100), -- Cambiado de INT a VARCHAR por lógica de negocio
    activo TINYINT(1) DEFAULT 1,
    
    FOREIGN KEY (instituto_id) REFERENCES Instituto(id)
) ENGINE=InnoDB;

-- 3. TABLA PRINCIPAL: Encuesta
CREATE TABLE Encuesta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    creado DATETIME DEFAULT CURRENT_TIMESTAMP,
    email VARCHAR(255) NOT NULL,
    nombres VARCHAR(255) NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    fecha_nacimiento DATE, -- Representado como Unix Timestamp o formato YYYYMMDD
    direccion TEXT,
    hijos TINYINT(1) DEFAULT 0,
    numero_hijos INT DEFAULT 0,
    discapacidad VARCHAR(255),
    enfermedad_cronica VARCHAR(255),
    estudio_fya TINYINT(1) DEFAULT 0,
    numero_habitantes INT DEFAULT 1,
    numero_ocupantes_familia INT DEFAULT 1,
    url_cedula VARCHAR(255),
    activo TINYINT(1) DEFAULT 1,
    
    -- Foreign Keys (FK)
    instituto_id INT,
    nacionalidad_id INT,
    sexo_id INT,
    tipo_estudiante_id INT,
    carrera_id INT,
    semestre_id INT,
    estado_civil_id INT,
    condicion_laboral_id INT,
    trabajo_relacion_id INT,
    tipo_organizacion_id INT,
    sector_trabajo_id INT,
    categoria_ocupacional_id INT,
    tipo_convivencia_id INT,
    tipo_vivienda_id INT,
    tenencia_vivienda_id INT,
    frecuencia_servicio_agua_id INT,
    frecuencia_servicio_aseo_id INT,
    frecuencia_servicio_electricidad_id INT,
    frecuencia_servicio_gas_id INT,
    transporte_id INT,
    dependencia_economica_id INT,
    fuente_ingreso_familiar_id INT,
    ingreso_familiar_id INT,
    nivel_eduacion_padre_id INT,
    trabaja_padre TINYINT(1) DEFAULT 0,
    tipo_empresa_padre_id INT,
    categoria_ocupacional_padre_id INT,
    sector_trabajo_padre_id INT,
    padre_en_venezuela TINYINT(1) DEFAULT 1,
    padre_egresado_iujo TINYINT(1) DEFAULT 0,
    nivel_eduacion_madre_id INT,
    trabaja_madre TINYINT(1) DEFAULT 0,
    tipo_empresa_madre_id INT,
    categoria_ocupacional_madre_id INT,
    sector_trabajo_madre_id INT,
    madre_en_venezuela TINYINT(1) DEFAULT 1,
    madre_egresada_iujo TINYINT(1) DEFAULT 0,
    veracidad_id INT,
    tipo_beca_id INT,
    
	FOREIGN KEY (instituto_id) REFERENCES Instituto(id),
    FOREIGN KEY (nacionalidad_id) REFERENCES Nacionalidad(id),
    FOREIGN KEY (sexo_id) REFERENCES Sexo(id),
    FOREIGN KEY (tipo_estudiante_id) REFERENCES TipoEstudiante(id),
    FOREIGN KEY (carrera_id) REFERENCES Carrera(id),
    FOREIGN KEY (semestre_id) REFERENCES Semestre(id),
    FOREIGN KEY (estado_civil_id) REFERENCES EstadoCivil(id),
    FOREIGN KEY (condicion_laboral_id) REFERENCES CondicionLaboral(id),
    FOREIGN KEY (trabajo_relacion_id) REFERENCES RelacionLaboral(id),
    FOREIGN KEY (tipo_organizacion_id) REFERENCES TipoOrganizacion(id),
    FOREIGN KEY (sector_trabajo_id) REFERENCES SectorTrabajo(id),
    FOREIGN KEY (categoria_ocupacional_id) REFERENCES CategoriaOcupacional(id),
    FOREIGN KEY (tipo_convivencia_id) REFERENCES TipoConvivencia(id),
    FOREIGN KEY (tipo_vivienda_id) REFERENCES TipoVivienda(id),
    FOREIGN KEY (tenencia_vivienda_id) REFERENCES TenenciaVivienda(id),
    FOREIGN KEY (frecuencia_servicio_agua_id) REFERENCES FrecuenciaServicioAgua(id),
    FOREIGN KEY (frecuencia_servicio_aseo_id) REFERENCES FrecuenciaServicioAseo(id),
    FOREIGN KEY (frecuencia_servicio_electricidad_id) REFERENCES FrecuenciaServicioElectricidad(id),
    FOREIGN KEY (frecuencia_servicio_gas_id) REFERENCES FrecuenciaServicioGas(id),
    FOREIGN KEY (transporte_id) REFERENCES Transporte(id),
    FOREIGN KEY (dependencia_economica_id) REFERENCES DependenciaEconomica(id),
    FOREIGN KEY (fuente_ingreso_familiar_id) REFERENCES FuenteIngresoFamiliar(id),
    FOREIGN KEY (ingreso_familiar_id) REFERENCES IngresoFamiliar(id),
    FOREIGN KEY (nivel_eduacion_padre_id) REFERENCES NivelEducacion(id),
    FOREIGN KEY (tipo_empresa_padre_id) REFERENCES TipoEmpresa(id),
    FOREIGN KEY (categoria_ocupacional_padre_id) REFERENCES CategoriaOcupacional(id),
    FOREIGN KEY (sector_trabajo_padre_id) REFERENCES SectorTrabajo(id),
    FOREIGN KEY (nivel_eduacion_madre_id) REFERENCES NivelEducacion(id),
    FOREIGN KEY (tipo_empresa_madre_id) REFERENCES TipoEmpresa(id),
    FOREIGN KEY (categoria_ocupacional_madre_id) REFERENCES CategoriaOcupacional(id),
    FOREIGN KEY (sector_trabajo_madre_id) REFERENCES SectorTrabajo(id),
    FOREIGN KEY (veracidad_id) REFERENCES Veracidad(id),
    FOREIGN KEY (tipo_beca_id) REFERENCES TipoBeca(id)
) ENGINE=InnoDB;

-- BLOQUE DE TABLAS Usuarios (Autenticación)

CREATE TABLE Rol (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE, -- 'Administrador Global', 'Administrador', 'Analista'
    codigo VARCHAR(50) NOT NULL UNIQUE, -- 'SUPER_ADMIN', 'ADMIN_SEDE', 'ANALISTA'
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE Usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ci VARCHAR(10) NOT NULL UNIQUE,
    nombre_completo VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL, -- Siempre usa password_hash() en PHP
    rol_id INT NOT NULL,
    instituto_id INT NULL, -- NULL si es SuperAdmin, de lo contrario lo amarras a su sede
    activo TINYINT(1) DEFAULT 1,
    creado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES Rol(id),
    FOREIGN KEY (instituto_id) REFERENCES Instituto(id)
) ENGINE=InnoDB;

-- 4. TABLAS DE RELACIÓN (Muchos a Muchos)
CREATE TABLE ConjuntoActivoVivienda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activo_vivienda_id INT,
    encuesta_id INT,
    UNIQUE (activo_vivienda_id, encuesta_id),
    FOREIGN KEY (activo_vivienda_id) REFERENCES ActivoVivienda(id),
    FOREIGN KEY (encuesta_id) REFERENCES Encuesta(id)
) ENGINE=InnoDB;

CREATE TABLE ConjuntoAmbienteVivienda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ambiente_vivienda_id INT,
    encuesta_id INT,
    UNIQUE (ambiente_vivienda_id , encuesta_id),
    FOREIGN KEY (ambiente_vivienda_id) REFERENCES AmbienteVivienda(id),
    FOREIGN KEY (encuesta_id) REFERENCES Encuesta(id)
) ENGINE=InnoDB;

CREATE TABLE ConjuntoServicioVivienda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servicio_vivienda_id INT,
    encuesta_id INT,
    UNIQUE (servicio_vivienda_id , encuesta_id),
    FOREIGN KEY (servicio_vivienda_id) REFERENCES ServicioVivienda(id),
    FOREIGN KEY (encuesta_id) REFERENCES Encuesta(id)
) ENGINE=InnoDB;

CREATE TABLE Instituto_Carrera (
    instituto_id INT NOT NULL,
    carrera_id INT NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    PRIMARY KEY (instituto_id, carrera_id),
    FOREIGN KEY (instituto_id) REFERENCES Instituto(id),
    FOREIGN KEY (carrera_id) REFERENCES Carrera(id)
) ENGINE=InnoDB;

CREATE INDEX idx_encuesta_instituto ON Encuesta(instituto_id);
CREATE INDEX idx_usuario_instituto ON Usuario(instituto_id);

SET FOREIGN_KEY_CHECKS = 1;