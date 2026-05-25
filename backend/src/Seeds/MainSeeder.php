<?php
namespace App\Seeds;

use App\Core\Database;
use PDO;

class MainSeeder {
    private $db;
    private $institutoId;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function run() {
        echo "Iniciando siembra masiva (Arquitectura multi-tenant IUJO 2026)...\n";

        // --- 0. EL TENANT (Instituto) ---
        // $this->seedInstitutos();

        // --- 1. SEGURIDAD (Roles y primer Usuario) ---
        $this->seedRolesYUsuarios();

        // --- 2. CATÁLOGOS GLOBALES (Simples) ---
        $this->seedSimple('Nacionalidad', ['Venezolana', 'Extranjera']);
        $this->seedSimple('Sexo', ['Femenino', 'Masculino']);
        $this->seedSimple('TipoEstudiante', [
            'Estudiante Regular', 'Estudiante Nuevo Ingreso', 
            'En proceso de preinscripción (nuevo ingreso)'
        ]);
        $this->seedSimple('EstadoCivil', ['Soltero', 'Casado', 'Unido / Concubinato', 'Divorciado', 'Viudo', 'Otra situación']);
        $this->seedSimple('TipoConvivencia', [
            'En el hogar de los padres', 'En el hogar propio', 
            'En el hogar de amigos o familiares sin alquilar', 
            'En el hogar de amigos o familiares en alquiler', 
            'En residencia estudiantil/pensión/hotel'
        ]);
        
        // --- 3. LABORAL Y ECONÓMICO ---
        $this->seedSimple('CondicionLaboral', [
            'Trabajando', 'Estudia', 'Trabajando, Estudia', 
            'Buscando trabajo', 'Buscando trabajo, Estudia', 
            'Trabajando, Buscando trabajo', 'Trabajando, Buscando trabajo, Estudia'
        ]);
        $this->seedSimple('RelacionLaboral', ['Total', 'Parcial', 'Ninguna', 'No trabaja']);
        $this->seedSimple('TipoOrganizacion', [
            'Empresa privada', 'Empresa pública', 
            'Ejercicio independiente Micro-Empresario', 
            'Ejercicio independiente en emprendimiento informal', 'No trabaja'
        ]);
        $this->seedSimple('SectorTrabajo', [
            'Banca y Finanzas', 'Comercio y Servicio', 'Agropecuario', 
            'Educación', 'Industrial y Empresarial', 'No trabaja'
        ]);
        $this->seedSimple('CategoriaOcupacional', [
            'Ayudante familiar', 'Empleado sector privado', 'Empleado sector público', 
            'Miembro de cooperativa', 'Obrero sector privado', 'Obrero sector público', 
            'Patrono o empleador', 'Trabajador por cuenta propia', 'No trabaja'
        ]);
        $this->seedSimple('DependenciaEconomica', ['Padre', 'Madre', 'Padre y madre', 'Usted mismo', 'Cónyuge', 'Otro familiar', 'Hermano/a', 'Otra persona']);
        $this->seedSimple('IngresoFamiliar', [
            '30 dólares o menos', 'Más de 30 dólares a 90 dólares', 
            'Más de 90 dólares a 150 dólares', 'Más de 90 dólares a 150 dólares', 
            'Más de 150 dólares a 210 dólares', 'Más de 210 dólares a 270 dólares', 
            'Más de 270 dólares a 330 dólares', 'Más de 330 dólares'
        ]);

        // --- 4. VIVIENDA Y SERVICIOS ---
        $this->seedSimple('TenenciaVivienda', ['Propia pagada', 'Propia pagándose', 'Alquilada', 'Prestada', 'De un familiar-acogido', 'Invasión']);
        $this->seedSimple('AmbienteVivienda', ['Sala', 'Comedor', 'Cocina', 'Baño', 'Lavandero']);
        $this->seedSimple('ActivoVivienda', ['Nevera', 'Lavadora', 'Secadora', 'Computador personal', 'Cocina', 'Aire Acondicionado', 'Televisor', 'Calentador', 'Horno Microondas']);
        $this->seedSimple('ServicioVivienda', ['Agua', 'Electricidad', 'Aseo / Recolección de Basura', 'Conexión a Internet', 'Telefonía Fija', 'Gas Directo', 'Gas Bombona (comunal)']);
        $this->seedSimple('FrecuenciaServicioAgua', ['Permanente (24 horas)', 'Diaria', 'Dos veces por semana', 'Una vez por semana', 'Cada quince días', 'Esporádicamente / Irregular']);
        $this->seedSimple('FrecuenciaServicioAseo', ['Diaria', 'Dos veces por semana', 'Una vez por semana', 'Cada quince días', 'Esporádicamente']);
        $this->seedSimple('FrecuenciaServicioElectricidad', ['Permanente (24 horas)', 'Corte - 4 horas', 'Corte - 8 horas', 'Corte - NO PROGRAMADOS']);
        $this->seedSimple('FrecuenciaServicioGas', ['Regular', 'Irregular', 'No aplica']);
        $this->seedSimple('Transporte', ['Vehículo - Propio', 'Vehículo - Familiar', 'Moto - Propia', 'Moto - Familiar', 'Transporte Público por puesto', 'Metro', 'Bicicleta', 'Ninguno']);

        // --- 5. CATÁLOGOS CON ESTRATOS (Méndez-Castellano / Graffar) ---
        $this->seedTableWithEstrato('TipoVivienda', [
            ['Quinta o Apartamento de lujo', 1],
            ['Casa-apto categoría intermedia', 2],
            ['Casa-apto interés social / Misión Vivienda', 3],
            ['Casa en zonas populares', 3],
            ['Vivienda rural', 4],
            ['Rancho (vivienda con deficiencias estructurales y sanitarias)', 5]
        ]);
        $this->seedTableWithEstrato('FuenteIngresoFamiliar', [
            ['Inversión en empresas/negocios/rentas', 1],
            ['Honorarios profesionales', 2],
            ['Sueldo quincenal o mensual', 3],
            ['Salario semanal o diario', 4],
            ['Trabajos ocasionales', 5],
            ['Otros ingresos', 5]
        ]);
        $this->seedTableWithEstrato('NivelEducacion', [
            ['Estudios de postgrado', 1],
            ['Licenciatura o Ingenieria completa', 1],
            ['TSU completa', 2],
            ['Licenciatura o Ingenieria incompleta', 2],
            ['TSU incompleta', 3],
            ['Secundaria completa', 3],
            ['Secundaria incompleta', 4],
            ['Primaria completa o menos', 4],
            ['No aplica', 5]
        ]);

        // --- 6. CATÁLOGOS LOCALES (Dependen del institutoId) ---
        $this->seedCarrerasConRelacion([
            'Administración de Empresas', 'Contaduría', 'Educación Especial', 
            'Educación Inicial', 'Educación Integral', 'Electrotecnia', 
            'Electrónica', 'Informática', 'Mecánica', 'Producción Agropecuaria'
        ]);
        
        $this->seedTipoBecaLocal([
            'No posee beca', 'Becado por la institución', 
            'Becado por un ente del Estado Centralizado (Sistema Patria)', 
            'Becado por un ente del Estado Descentralizado (Alcaldía o Gobernación)', 
            'Becado por ente privado', 'QG'
        ]);

        // --- 7. OTROS Y VALIDACIÓN ---
        $this->seedSimple('TipoEmpresa', ['Pública', 'Privada', 'Ejercicio Independiente', 'No trabaja', 'No aplica']);
        $this->seedSimple('Veracidad', ['Sí, doy fe de la veracidad de la información y autenticidad de los documentos']);
        $this->seedSemestres();

        echo "✅ Siembra completada con éxito. Catálogos sincronizados con el IUJO 2-2025.\n";
    }

    // --- MÉTODOS DE APOYO (HELPERS) ---

    // private function seedInstitutos() {
    //     $stmt = $this->db->prepare("INSERT IGNORE INTO Instituto (siglas, nombre) VALUES (?, ?)");
    //     $stmt->execute(['IUJO-BARQUISIMETO', 'Instituto Universitario Jesús Obrero Barquisimeto']);
    //     $this->institutoId = $this->db->lastInsertId() ?: $this->db->query("SELECT id FROM Instituto WHERE siglas = 'IUJO-BARQUISIMETO'")->fetchColumn();
    // }

    private function seedRolesYUsuarios() {
        $this->ensureRolCodigoColumn();

        $roles = [ 'Administrador Global' => 'SUPER_ADMIN', 'Administrador' => 'ADMIN_SEDE', 'Analista' => 'ANALISTA' ];
        foreach ($roles as $nombre => $codigo) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO Rol (nombre, codigo) VALUES (?, ?)");
            $stmt->execute([$nombre, $codigo]);
        }
        $rolId = $this->db->query("SELECT id FROM Rol WHERE codigo = 'SUPER_ADMIN'")->fetchColumn();
        $pass = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT IGNORE INTO Usuario (ci, nombre_completo, password, rol_id, instituto_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['12345678', 'Admin IUJO BQTO', $pass, $rolId, $this->institutoId]);
    }

    private function ensureRolCodigoColumn() {
        if ($this->hasColumn('Rol', 'codigo')) {
            return;
        }

        $this->db->exec("ALTER TABLE Rol ADD COLUMN codigo VARCHAR(50) NULL AFTER nombre");

        $mappings = [
            'Administrador Global' => 'SUPER_ADMIN',
            'Administrador' => 'ADMIN_SEDE',
            'Analista' => 'ANALISTA',
        ];

        foreach ($mappings as $nombre => $codigo) {
            $stmt = $this->db->prepare("UPDATE Rol SET codigo = ? WHERE nombre = ? AND (codigo IS NULL OR codigo = '')");
            $stmt->execute([$codigo, $nombre]);
        }

        $countStmt = $this->db->query("SELECT COUNT(*) FROM Rol WHERE codigo IS NULL OR codigo = ''");
        $missing = $countStmt ? (int)$countStmt->fetchColumn() : 0;
        if ($missing === 0) {
            $this->db->exec("ALTER TABLE Rol MODIFY codigo VARCHAR(50) NOT NULL");
        }
    }

    private function hasColumn($table, $column) {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(string)$table, (string)$column]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function seedSimple($table, $items) {
        foreach ($items as $item) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO $table (nombre) VALUES (?)");
            $stmt->execute([$item]);
        }
    }

    private function seedTableWithEstrato($table, $data) {
        foreach ($data as $row) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO $table (nombre, valor_estrato) VALUES (?, ?)");
            $stmt->execute($row);
        }
    }

    private function seedCarrerasConRelacion($carreras) {
        foreach ($carreras as $nombre) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO Carrera (nombre) VALUES (?)");
            $stmt->execute([$nombre]);
            $carreraId = $this->db->query("SELECT id FROM Carrera WHERE nombre = '$nombre'")->fetchColumn();
            
            $stmt = $this->db->prepare("INSERT IGNORE INTO Instituto_Carrera (instituto_id, carrera_id) VALUES (?, ?)");
            $stmt->execute([$this->institutoId, $carreraId]);
        }
    }

    private function seedTipoBecaLocal($becas) {
        foreach ($becas as $beca) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO TipoBeca (nombre, instituto_id) VALUES (?, ?)");
            $stmt->execute([$beca, $this->institutoId]);
        }
    }

    private function seedSemestres() {
        $semestres = [
            ['1ER SEMESTRE', 1], ['2DO SEMESTRE', 2], ['3ER SEMESTRE', 3], 
            ['4TO SEMESTRE', 4], ['5TO SEMESTRE', 5], ['6TO SEMESTRE', 6]
        ];
        foreach ($semestres as $s) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO Semestre (nombre, numero) VALUES (?, ?)");
            $stmt->execute($s);
        }
    }
}