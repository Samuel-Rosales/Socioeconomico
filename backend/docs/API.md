# API (BACKEND-SOCIOECONOMICO)

Esta guía documenta los endpoints del backend según el código actual.

## Base URL (XAMPP)

Si el proyecto está dentro de `htdocs/` y se accede directo a `public/`:

- `http://localhost/BACKEND-SOCIOECONOMICO/public`

> El Router ajusta el `basePath` automáticamente, pero el punto de entrada siempre es `public/index.php`.

## Formato de respuestas

Todos los endpoints responden JSON con el formato:

Éxito:

```json
{ "success": true, "data": {}, "message": "..." }
```

Error:

```json
{ "success": false, "data": { "errors": {} }, "message": "..." }
```

Notas:
- El flag `success` del JSON no reemplaza el código HTTP (se usan ambos).
- El Router también responde en este formato para `404` y `405`.

## Endpoints

### GET /catalogo/:resource

Devuelve los registros activos (`activo = 1`) del catálogo solicitado.

Multi-tenant:
- Algunos catálogos dependen del Instituto (tenant) actual.
- Puedes indicar el tenant con:
  - Header `X-Instituto-Id: <id>` (recomendado)
  - Querystring `?instituto_id=<id>`

- Método: `GET`
- Path params:
  - `resource`: nombre del catálogo (string)
- Respuesta (200):

```json
{
  "success": true,
  "data": [
    { "id": 1, "nombre": "...", "activo": 1 }
  ],
  "message": "Catálogo obtenido correctamente"
}
```

- Respuesta (404):

```json
{
  "success": false,
  "data": {
    "errors": {
      "catalogo": ["El catálogo 'xxx' no existe en el sistema."]
    }
  },
  "message": "Catálogo no encontrado"
}
```

Ejemplo:

- `GET /catalogo/nacionalidad`

#### Resources mapeados

Los resources se definen en `src/Services/CatalogService.php`. A nivel de intención, incluye:

- Identificación y académico:
  - `nacionalidad`, `sexo`, `tipo-estudiante`, `carrera`, `semestre`
- Situación civil y laboral (pendiente de modelos en este repo):
  - `estado-civil`, `condicion-laboral`, `relacion-laboral`, `tipo-organizacion`, `sector-trabajo`, `categoria-ocupacional`
- Vivienda y convivencia:
  - `tipo-convivencia`, `tipo-vivienda`, `tenencia-vivienda`, `ambiente-vivienda`, `activo-vivienda`, `servicio-vivienda`
- Servicios y frecuencias:
  - `frecuencia-agua`, `frecuencia-aseo`, `frecuencia-electricidad`, `frecuencia-gas`, `transporte`
- Economía y educación:
  - `dependencia-economica`, `fuente-ingreso`, `ingreso-familiar`, `nivel-educacion`, `tipo-empresa`
- Otros:
  - `veracidad`, `tipo-beca`

#### Catálogos tenant-scoped

Estos recursos se filtran por `instituto_id`:

- `carrera` (usa la relación `Instituto_Carrera`)
- `tipo-beca` (columna `instituto_id` en `TipoBeca`)

> Nota: varios de los modelos de “situación civil y laboral” no están presentes en `src/Models/` en el estado actual del repo; si se solicita el resource, responderá `500`.

### POST /encuesta

Registra una encuesta.

Multi-tenant (obligatorio para este endpoint):
- Debes indicar el Instituto (tenant) de una de estas formas:
  - Header `X-Instituto-Id: <id>`
  - Header `X-Instituto-Siglas: <siglas>` (recomendado si tu frontend usa `/:sede/...`)
  - Querystring `?instituto_id=<id>` o `?instituto_siglas=<siglas>`
  - Body `instituto_id` (numérico)

> Nota: este endpoint NO usa fallback al “primer instituto activo”. Si no se indica tenant, responde `400`.

#### Validación (resumen)

`EncuestaService` valida un conjunto grande de campos. Reglas soportadas por `Validator`:

- `required`
- `numeric`
- `email`

Además, el service separa relaciones M:N esperadas en el request:

- `activos`: array de IDs
- `servicios`: array de IDs
- `ambientes`: array de IDs

#### Persistencia

`EncuestaModel::guardarCompleta()` usa transacción e inserta:

- fila principal en `Encuesta`
- filas en tablas puente:
  - `ConjuntoActivoVivienda`
  - `ConjuntoServicioVivienda`
  - `ConjuntoAmbienteVivienda`

### GET /encuestas

Lista encuestas en formato **resumido** para tablas/dashboards.

- Método: `GET`
- Auth: requiere `Authorization: Bearer <token>`
- Roles permitidos: `SUPER_ADMIN`, `ADMIN_SEDE`, `ANALISTA`

Multi-tenant:
- `ADMIN_SEDE` / `ANALISTA`: lista solo su `instituto_id` (del token).
- `SUPER_ADMIN`: lista todas **a menos** que indique un tenant explícito con `X-Instituto-Id` o `?instituto_id=`.

Incluye cálculo de estrato basado en los catálogos con `valor_estrato`:
- `TipoVivienda`
- `FuenteIngresoFamiliar`
- `NivelEducacion` (padre y madre)

Respuesta (200):

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "creado": "2026-03-12 10:22:11",
        "nombres": "Juan",
        "apellidos": "Pérez",
        "estudiante": "Juan Pérez",
        "cedula": "20123456",
        "carrera_id": 3,
        "carrera": "Informática",
        "instituto_id": 1,
        "instituto_siglas": "IUJO-BARQUISIMETO",
        "instituto_nombre": "...",
        "estrato_puntaje": 10,
        "estrato": 3
      }
    ]
  },
  "message": "Encuestas listadas correctamente"
}
```

## Códigos de error

- `404` cuando la ruta no existe o el catálogo no está mapeado.
- `405` cuando el método no está permitido.
- `400` para validación.
- `401` para autenticación.
- `403` para autorización / usuario inactivo.

### POST /login

Inicia sesión contra la tabla `Usuario`.

- Método: `POST`
- Body (JSON recomendado):
  - `ci` (string) requerido
  - `password` (string) requerido
- Multi-tenant:
  - Si envías `X-Instituto-Id` (o `?instituto_id=`) y el usuario está asociado a una sede, se valida que coincidan.
  - Si NO envías tenant, el login no aplica fallback: el tenant efectivo quedará determinado por el usuario.

Respuesta 200:

```json
{
  "success": true,
  "data": {
    "token": "<bearer_token>",
    "user": {
      "id": 1,
      "ci": "12345678",
      "nombre_completo": "Admin IUJO BQTO",
      "rol": { "id": 2, "codigo": "ADMIN_SEDE", "nombre": "Administrador" },
      "instituto": { "id": 1, "siglas": "IUJO-BARQUISIMETO", "nombre": "..." }
    }
  },
  "message": "Inicio de sesión exitoso"
}
```

Usa el token en endpoints protegidos:

`Authorization: Bearer <bearer_token>`

Errores comunes:
- 400: faltan campos
- 401: credenciales inválidas
- 403: usuario inactivo o instituto no coincide

### CRUD /usuarios

Requiere autenticación Bearer.

Roles permitidos (por `Rol.codigo`):
- `SUPER_ADMIN`
- `ADMIN_SEDE`

Endpoints para gestionar la tabla `Usuario`. Todas las operaciones son **soft-delete** (se marca `activo = 0`).

Multi-tenant:
- `ADMIN_SEDE`: el tenant viene del token, no se puede sobre-escribir.
- `SUPER_ADMIN`: puede indicar el tenant explícitamente con `X-Instituto-Id` o `?instituto_id=`.
- Para roles distintos de `SUPER_ADMIN`, `instituto_id` es obligatorio.

> Nota: el backend NO retorna `password` nunca en estas respuestas.

#### GET /usuarios

- Lista usuarios activos.
- Si hay tenant, filtra por `instituto_id`.

#### GET /usuarios/:id

- Obtiene un usuario activo por id (respeta tenant si viene).

#### POST /usuarios

Body JSON recomendado:

```json
{
  "ci": "12345678",
  "nombre_completo": "Nombre Apellido",
  "password": "textoPlano",
  "rol_id": 2,
  "instituto_id": 1
}
```

#### PUT /usuarios/:id

Actualiza uno o más campos (todos opcionales):

```json
{
  "nombre_completo": "Nuevo Nombre",
  "password": "nuevoPassword",
  "rol_id": 3,
  "instituto_id": 1,
  "activo": 1
}
```

#### DELETE /usuarios/:id

Marca `activo = 0`.
