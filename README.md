# Manager Library API — HU-01

Backend Laravel con arquitectura hexagonal según architecture y arquitectura-panoramica de spec-.net y spec-python. La panorámica indica Laravel para el reto. Rama local: `hu-01-dev`.

## Organización

```text
src/Library/
  Domain/                         User puro, sin Laravel ni Eloquent
  Application/Ports/Outbound/     UserRepository, PasswordHasher, TokenIssuer, TokenVerifier, Clock
  Application/Services/           LoginService, ProvisionAdminService
  Adapters/Inbound/Http/           Controlador, protección por token/rol, errores del contrato
  Adapters/Inbound/Console/        Provisionar admin desde entorno
  Adapters/Outbound/Persistence/   Repositorio y seis modelos Eloquent con relaciones
  Adapters/Outbound/Security/      BCrypt factor 12, JWT HS256, reloj UTC
  Bootstrap/Composition/          PortBindings: única unión de puertos y adaptadores
database/migrations/              Esquema library y una migración por tabla
routes/api.php                    /api/v1/auth/login y /api/v1/health
```

SRP: un servicio por caso de uso. DIP/ISP: dependencias a puertos pequeños del núcleo. OCP: adaptadores intercambiables sin cambiar aplicación/dominio. LSP: pruebas con dobles de puertos y repositorio real. Pruebas de arquitectura verifican dependencias hacia adentro y composición única.

## Migraciones y ADR

- ADR-001: solo el backend crea el esquema, las seis tablas, índices, restricciones y cinco categorías. Infra solo prepara PostgreSQL vacío, usuario y volumen. La tabla técnica public.migrations registra el historial de Laravel, fuera del esquema library.
- ADR-002: el CHECK ck_book_available_range protege el inventario. Para la futura HU-06 se usará xmin de PostgreSQL como testigo optimista, sin añadir una columna no documentada. El adaptador de préstamo debe comparar la versión y guardar inventario/líneas en una transacción; ese caso de uso y su prueba concurrente aún están pendientes.
- ADR-003: BookModel tiene filtro global SoftDeletes por deleted_at; ISBN único solo entre activos; FK RESTRICT desde loan_item. La relación histórica recupera incluso libros dados de baja. La operación de baja y su rechazo con préstamos abiertos pertenecen a HU-03, pendiente.
- ADR-004: loan_item conserva book_title, isbn y category_name, sin FK de categoría ni tabla de reporte. El puerto de reporte y consulta agregada por book_id + book_title + category_name se implementarán en HU-10.

Se respetan exactamente las columnas del modelo: UUID, timestamptz, sin DEFAULT, created_at, updated_at, status ni days_late. ck_book_total_positive y ck_user_role siguen pendientes de T-18 como indica el documento; no se cambió su estado silenciosamente. Las invariantes de User están en el dominio.

## Ejecutar con PHP local

Requisitos: PHP 8.3 con pdo_pgsql, mbstring, dom, xml y xmlwriter; Composer 2; PostgreSQL 16 de infra iniciado.

En PowerShell:

```powershell
Copy-Item .env.example .env
```

En Bash: `cp .env.example .env`.

Editar .env: DB_HOST=localhost para API local; mismo DB_DATABASE/DB_USERNAME/DB_PASSWORD que infra. DATABASE_URL es opcional y puede reemplazar esos campos. Configurar JWT_SECRET aleatorio de al menos 32 caracteres, ADMIN_USERNAME y ADMIN_PASSWORD propios; CORS_ORIGIN=http://localhost:5173. No versionar .env.

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan library:provision-admin
php artisan serve --host=0.0.0.0 --port=8000
```

El administrador lo crea la aplicación con hash; no la migración. El comando es idempotente: no reemplaza la clave de un admin existente. La imagen de ejecución también lo llama al iniciar; exige haber migrado antes.

## Ejecutar con Docker

Desde infra, configurar .env y ejecutar docker compose up -d --wait. Desde api, copiar y completar .env, usando DB_HOST=db y las mismas credenciales:

```bash
docker build -t manager-library-api .
docker run --rm --network manager-library --env-file .env manager-library-api php artisan key:generate --show
```

Guardar la clave generada en APP_KEY de .env. Después:

```bash
docker run --rm --network manager-library --env-file .env manager-library-api php artisan migrate --force
docker run --rm --network manager-library --env-file .env -p 8000:8000 manager-library-api
```

No hay DDL en infra ni creación automática del esquema por Eloquent. El servidor PHP incorporado permite probar localmente; al publicar, configurar servidor web y HTTPS para cumplir RNF-3.

## Contrato de HU-01 y prueba manual

GET http://localhost:8000/health es público.

POST http://localhost:8000/api/v1/auth/login con Content-Type: application/json:

```json
{"username":"<ADMIN_USERNAME>","password":"<ADMIN_PASSWORD>"}
```

CA-01.1: devuelve access_token, token_type Bearer, expires_in 28800 y role admin. JWT firmado HS256 con sub y role, sin usuario, contraseña ni hash.

CA-01.2: usuario inexistente o clave incorrecta producen exactamente 401 INVALID_CREDENTIALS con mensaje genérico y details vacío.

CA-01.3: toda ruta salvo login y salud exige Authorization: Bearer <token>. Sin token, alterado o vencido: 401. Rol librarian contra POST /users, escrituras de /books o /reports/most-borrowed: 403 FORBIDDEN_ROLE antes de buscar recursos.

Las rutas de las HU restantes todavía no tienen controladores: una solicitud autorizada recibe 404 NOT_FOUND. No se devuelve éxito simulado ni se declaran implementadas esas historias. Todos los errores usan code, message y details sin datos sensibles. CORS solo admite el origen configurado. React de app ya puede probar el login.

## Pruebas aisladas

Con Docker, desde este repositorio:

```bash
docker compose -p manager-library-hu01-tests -f compose.test.yml up --build --abort-on-container-exit --exit-code-from tests
docker compose -p manager-library-hu01-tests -f compose.test.yml down
```

La base de pruebas no publica puertos ni usa el volumen de infra. Sus credenciales son fixtures desechables. La suite cubre los CA de HU-01, JWT inválido/vencido, permisos, CORS, esquema y restricciones, admin idempotente, aplicación con dobles y arquitectura. No ejecutar esa suite sobre una base con datos de uso.

Con PHP local, configurar una base exclusiva de pruebas y las variables fixture de compose.test.yml, migrarla/provisionarla y ejecutar `composer test`.

HU-02 a HU-10, préstamo concurrente, consultas del reporte y T-20 completo siguen pendientes. Esta entrega no acredita RNF-1 ni RNF-2.
