# Manager Library API

Backend Laravel para Manager Library.

## HU-01 - Health check

Primer corte del backend: preparar la aplicacion y exponer una ruta publica de salud.

```http
GET /health
```

Respuesta esperada:

```json
{ "status": "ok" }
```

## Ejecutar con Docker

```bash
docker build -t manager-library-api .
docker run --rm -p 8000:8000 --env-file .env.example manager-library-api
```

Luego abrir:

```text
http://localhost:8000/health
```

## Reglas del spec aplicadas desde el inicio

- El esquema de base de datos se creara con migraciones de Laravel, no desde `infra`.
- Las rutas y campos del API usan ingles.
- Los mensajes y documentacion se mantienen en espanol.
- Los secretos reales no se versionan; este repositorio solo incluye `.env.example`.
