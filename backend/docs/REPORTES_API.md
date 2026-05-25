# API de Reportes

Modulo de reportes desacoplado de encuestas y estadisticas mock.

## Base URL

- `http://localhost/BACKEND-SOCIOECONOMICO/public`

## Seguridad y tenant

Todos los endpoints de reportes:

- Requieren `Authorization: Bearer <token>`
- Roles permitidos: `SUPER_ADMIN`, `ADMIN_SEDE`, `ANALISTA`
- Multi-tenant:
- `ADMIN_SEDE` y `ANALISTA`: se filtra por `instituto_id` del token
- `SUPER_ADMIN`: puede filtrar por tenant explicito con `X-Instituto-Id` o `?instituto_id=`

## Filtros comunes por query

- `from` (Y-m-d)
- `to` (Y-m-d)
- `instituto_id` (solo util para `SUPER_ADMIN`)
- `facultad_id` (aceptado por contrato; actualmente no hay entidad Facultad en schema)
- `carrera_id`

## GET /reportes/dashboard-general

Respuesta:

```json
{
  "success": true,
  "data": {
    "kpis": {
      "total_encuestados": 120,
      "total_poblacion": 154,
      "tasa_respuesta": 77.92,
      "moda_estrato": "3"
    },
    "sexo": {
      "labels": ["Femenino", "Masculino"],
      "values": [72, 48]
    },
    "estratos": {
      "labels": ["1", "2", "3", "4", "5", "Sin dato"],
      "values": [5, 16, 44, 30, 20, 5]
    }
  },
  "message": "Reporte dashboard general generado correctamente"
}
```

## GET /reportes/analisis-academico

Respuesta:

```json
{
  "success": true,
  "data": {
    "labels": ["Administracion", "Informatica"],
    "datasets": [
      {"label": "Estrato 1", "key": "1", "values": [15.5, 21.3]},
      {"label": "Estrato 2", "key": "2", "values": [25.0, 20.0]},
      {"label": "Estrato 3", "key": "3", "values": [32.0, 30.1]},
      {"label": "Estrato 4", "key": "4", "values": [17.5, 18.6]},
      {"label": "Estrato 5", "key": "5", "values": [7.0, 8.0]},
      {"label": "Sin dato", "key": "Sin dato", "values": [3.0, 2.0]}
    ]
  },
  "message": "Reporte de analisis academico generado correctamente"
}
```

## GET /reportes/demografico-vulnerabilidad

Respuesta:

```json
{
  "success": true,
  "data": {
    "heatmap": {
      "rows": ["Administracion", "Informatica"],
      "columns": ["1", "2", "3", "4", "5", "Sin dato"],
      "values": [
        [2, 6, 13, 9, 3, 1],
        [3, 5, 10, 11, 7, 2]
      ]
    },
    "sexo_por_estrato": {
      "labels": ["1", "2", "3", "4", "5", "Sin dato"],
      "femenino": [3, 7, 12, 9, 6, 1],
      "masculino": [2, 4, 11, 11, 4, 2]
    }
  },
  "message": "Reporte demografico y vulnerabilidad generado correctamente"
}
```

## GET /reportes/filtros

Respuesta:

```json
{
  "success": true,
  "data": {
    "institutos": [
      {"id": 1, "nombre": "IUJO", "siglas": "IUJO"}
    ],
    "facultades": [],
    "carreras": [
      {"id": 1, "nombre": "Informatica"}
    ],
    "estratos": ["1", "2", "3", "4", "5", "Sin dato"]
  },
  "message": "Filtros de reportes obtenidos correctamente"
}
```
