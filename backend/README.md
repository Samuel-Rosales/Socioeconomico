# BACKEND-SOCIOECONOMICO

Backend en PHP (arquitectura ligera tipo MVC) para el sistema socioeconómico. Este documento está pensado para colaboradores: cómo levantar el proyecto en XAMPP, cómo funciona internamente y cómo extenderlo (rutas, catálogos, encuestas).

## Stack y requisitos

- PHP 7.1+ (el repo está orientado a PHP 7.1)
- MySQL/MariaDB
- Apache (ej. XAMPP)
- Composer

## Instalación (Windows + XAMPP)

1) Instalar dependencias PHP:

```bash
cd BACKEND-SOCIOECONOMICO
composer install
```

2) Base de datos

- El backend se conecta a MySQL usando credenciales hardcodeadas en `src/Core/Database.php`.
- Por defecto:
	- host: `localhost`
		- db: `socioeconomico_db`
	- user: `root`
	- pass: `` (vacío)
	- charset: `utf8mb4`

Crear la base de datos (ejemplo):

```sql
CREATE DATABASE socioeconomico_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3) Configurar Apache / DocumentRoot

La entrada del backend es `public/index.php`.

Opciones comunes:

- Opción A: acceder con la carpeta `public/` en la URL (rápido en XAMPP)
	- `http://localhost/BACKEND-SOCIOECONOMICO/public/...`
- Opción B: configurar un VirtualHost o Alias apuntando a `BACKEND-SOCIOECONOMICO/public`

## Seed (datos iniciales)

El proyecto incluye un seeder básico para catálogos.

Ejecutar desde la raíz del backend:

```bash
php seed.php
```

Esto ejecuta `src/Seeds/MainSeeder.php` y hace inserts `INSERT IGNORE` en varias tablas catálogo.

También crea roles y un usuario admin inicial:

- CI: `12345678`
- Password: `admin123`
- Rol: `ADMIN_SEDE` (por `Rol.codigo`)

### Seed de institutos adicionales (sin resembrar todo)

Si ya corriste `php seed.php` y solo quieres **agregar otras sedes** (tabla `Instituto`) y sus catálogos *tenant-scoped*:

- `Instituto_Carrera` (carreras por instituto)
- `TipoBeca` (por instituto)

Puedes ejecutar:

```bash
php seed_institutos.php
```

Opcional:

```bash
php seed_institutos.php --dry-run
php seed_institutos.php --source-siglas=IUJO-BARQUISIMETO
php seed_institutos.php --config="institutos.json"
```

El script copia carreras/becas desde el instituto “origen” (por defecto `IUJO-BARQUISIMETO`).

## Importación masiva (CSV)

Para migrar un dataset de encuestas a la base de datos existe un script CLI:

```bash
php import_encuestas.php --file="encuestas.csv" --delimiter=";" --dry-run
php import_encuestas.php --file="encuestas.csv" --delimiter=";" --default-instituto-id=1
```

Genera un reporte JSON con errores/duplicados y soporta resolución de FKs por ID o por nombre.

Guía completa: `docs/IMPORT_ENCUESTAS.md`.

## Estructura del proyecto

- `public/`
	- `index.php`: front controller. Registra rutas en el Router y ejecuta `$router->run()`.
- `src/Core/`
	- `Router.php`: router minimalista (GET/POST/PUT/DELETE + parámetros con `:param`)
	- `Database.php`: conexión PDO singleton
	- `TenantContext.php`: resolución del tenant (instituto) desde headers/query/body (con fallback opcional)
	- `Validator.php`: validador simple por reglas (`required|numeric|email`)
	- `AuthToken.php`: emisión/verificación de token Bearer (HS256 tipo JWT)
	- `Auth.php`: middleware ligero de autorización (valida token + RBAC)
- `src/Controllers/`
	- Controladores HTTP (reciben request, devuelven respuesta)
- `src/Services/`
	- Lógica de negocio (orquesta validación + modelos)
- `src/Models/`
	- Acceso a datos (PDO) y operaciones CRUD
- `src/Seeds/`
	- Seeders para catálogos

## Flujo de una request (alto nivel)

1. Apache sirve `public/index.php`
2. `Router` compara el método + path con rutas registradas
3. Si coincide, hace dispatch a `Controlador@metodo`
4. El controller delega en un service (cuando aplica)
5. El service valida y llama a modelos
6. Los modelos usan PDO (singleton) para consultar/guardar

## API

La documentación de endpoints vive en `docs/API.md`.

## Cómo agregar/editar rutas

Las rutas se registran manualmente en `public/index.php`:

```php
$router->get('/catalogo/:resource', 'App\\Controllers\\CatalogController@index');
$router->post('/encuestas', 'App\\Controllers\\EncuestaController@registrar');
```

Notas:

- El Router soporta rutas dinámicas tipo `/catalogo/:resource`.
- Para APIs REST, también hay helpers `put()` y `delete()`.
- En XAMPP, el Router calcula un `basePath` a partir de `SCRIPT_NAME` para que funcione cuando el proyecto vive dentro de una subcarpeta.

## Autenticación y autorización (RBAC)

- Login: `POST /login` retorna `{ token, user }`.
- Endpoints protegidos (por ahora): CRUD de `/usuarios`.
- Header requerido:
	- `Authorization: Bearer <token>`

Roles (según `Rol.codigo`):

- `SUPER_ADMIN`
- `ADMIN_SEDE`
- `ANALISTA`

Nota importante: la app usa `Rol.codigo` para autorización (no `Rol.nombre`).

### Secret del token

El secreto se lee en este orden:

1) Variable de entorno `AUTH_SECRET` (o `JWT_SECRET`)

## Multi-tenant (Instituto)

`TenantContext` resuelve el `instituto_id` por prioridad:

- Header `X-Instituto-Id` / `X-Tenant-Id`
- Query `?instituto_id=`
- Body `instituto_id`
- Fallback: primer instituto activo (solo cuando se permite)

## Cómo agregar un catálogo nuevo

El endpoint de catálogos usa `CatalogService` y un mapa `resource -> Model`.

Pasos:

1) Crear el Model en `src/Models/` extendiendo `BaseModel` y declarando `$table`:

```php
class MiCatalogoModel extends BaseModel {
		protected $table = 'MiCatalogo';
}
```

2) Registrar el resource en `src/Services/CatalogService.php`:

```php
'mi-catalogo' => 'App\\Models\\MiCatalogoModel'
```

3) Asegurar que la tabla exista y cumpla el contrato usado por `BaseModel::getAll()`:

- columna `activo` (soft delete)
- (típicamente) columna `nombre`

## Convenciones de base de datos (según el código)

- Catálogos: `BaseModel::getAll()` filtra por `activo = 1`.
	- Implica que las tablas deben tener `activo` y su default debería ser `1`.
- Encuesta:
	- Tabla principal: `Encuesta`
	- Relaciones M:N (usadas por `EncuestaModel::guardarCompleta`):
		- `ConjuntoActivoVivienda (encuesta_id, activo_vivienda_id)`
		- `ConjuntoServicioVivienda (encuesta_id, servicio_vivienda_id)`
		- `ConjuntoAmbienteVivienda (encuesta_id, ambiente_vivienda_id)`

## Estado actual / pendientes conocidos (importante para colaboradores)

El backend está en evolución y hay algunos puntos a corregir/terminar:

- Aún faltan endpoints administrativos para gestionar respuestas de encuestas desde el backend.
- La administración de catálogos desde API (CRUD de catálogos) no está implementada como REST; hoy se consumen por `GET /catalogo/:resource`.

Si vas a trabajar en estas partes, prioriza: (1) alinear rutas con controllers, (2) alinear recursos del catálogo con modelos reales, (3) estandarizar nombres de archivos/clases según PSR-4.
