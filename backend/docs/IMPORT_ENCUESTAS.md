# Importación masiva de encuestas (CSV)

Este backend incluye un script CLI para migrar un dataset (CSV) hacia la tabla `Encuesta` respetando llaves foráneas (catálogos) y generando un reporte de errores.

## Requisitos previos

1) Crear la base de datos y tablas (ejecutar `script-db.sql`).
2) Sembrar catálogos (recomendado):

```bash
cd BACKEND-SOCIOECONOMICO
php seed.php
```

> Si tu CSV trae valores de catálogos como texto (por ejemplo `"Femenino"`), el importador necesita que existan previamente en sus tablas catálogo.

## Ejecutar importación

Desde la raíz del backend:

```bash
php import_encuestas.php --file="RUTA\\A\\encuestas.csv" --delimiter=";" --default-instituto-id=1
```

- `--delimiter`:
	- Export de Excel/Google Forms en español suele venir con `;`.
- `--default-instituto-id`:
	- Se usa si tu dataset no trae `instituto_id`.

### Dry-run (recomendado primero)

Valida y reporta errores sin insertar nada:

```bash
php import_encuestas.php --file="encuestas.csv" --delimiter=";" --dry-run
```

## Headers del CSV

El script intenta mapear automáticamente los headers del CSV a columnas de `Encuesta` cuando:

- El header coincide exactamente con la columna (por ejemplo `email`, `cedula`, `sexo_id`, etc.)
- O el header coincide con aliases usados por el frontend (ej. `relacion_laboral_id` → `trabajo_relacion_id`).

Si tu CSV usa headers “humanos” (por ejemplo preguntas de Google Forms), usa un mapa.

## Usar un mapa de columnas (`--map`)

Crea un JSON con pares `"Header del CSV" -> "columna_bd"`.

Ejemplo (plantilla):

```json
{
  "columns": {
    "HEADER_EMAIL": "email",
    "HEADER_NOMBRES": "nombres",
    "HEADER_APELLIDOS": "apellidos",
    "HEADER_CEDULA": "cedula",

    "HEADER_SEXO": "sexo_id",
    "HEADER_NACIONALIDAD": "nacionalidad_id",
    "HEADER_CARRERA": "carrera_id",

    "HEADER_ACTIVOS_VIVIENDA": "rel:activos",
    "HEADER_SERVICIOS_VIVIENDA": "rel:servicios",
    "HEADER_AMBIENTES_VIVIENDA": "rel:ambientes"
  }
}
```

Luego:

```bash
php import_encuestas.php --file="encuestas.csv" --delimiter=";" --map="mi_mapa.json"
```

### Valores de catálogos: ID o Nombre

Para columnas `*_id` puedes traer:

- ID numérico (ej. `sexo_id = 2`), o
- Texto (ej. `sexo_id = "Femenino"`) y el script lo resuelve por `nombre` en la tabla catálogo.

## Reporte

El script siempre genera un archivo JSON con:

- Resumen (`rows_ok`, `rows_failed`, etc.)
- Primeras N filas con errores (por defecto 500) con el número de fila del CSV y mensajes

Puedes elegir ruta:

```bash
php import_encuestas.php --file="encuestas.csv" --report="C:\\tmp\\import_report.json"
```
