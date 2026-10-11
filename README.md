# Delta Notifications

Backoffice y API para enviar push notifications por **FCM HTTP v1** (iOS vía APNs y Android). Cada cliente usa su propio proyecto de Firebase. La cola corre sobre la base de datos: no hace falta Redis.

## Stack

- Laravel 13, PHP 8.3
- Blade, Livewire 4, Alpine y Tailwind 4 (mismo esquema de backoffice que Desert: layout con sidebar, CRUDs en controllers, filtros por GET, permisos por rol)
- Cola `database`
- FCM por HTTP directo con OAuth2 de service account (credenciales distintas por cliente, y el request/response queda en los logs)

## Requisitos

- PHP 8.3+ con `openssl`, `mbstring`, `pdo`, `tokenizer`, `xml`, `curl`, `fileinfo`
- Composer 2
- Node 20+ y npm
- MySQL (o SQLite para pruebas)

## Instalación

```bash
composer install
npm install
npm run build

cp .env.example .env
php artisan key:generate

# Crear la base y completar DB_* en .env
php artisan migrate --seed
```

El seeder crea:

- un administrador: `admin@push.test` / `password` (cambialos con `ADMIN_EMAIL` y `ADMIN_PASSWORD`)
- un cliente demo. La API key local es `pns_demo_local_key_change_me` (`DEMO_CLIENT_API_KEY`)

Entrá a `/login`. La API key de un cliente nuevo se muestra una sola vez al crearla o al regenerarla. La service account se guarda encriptada y no vuelve a verse.

## Cola y scheduler

Los envíos no salen en el request: quedan en `push_notifications` y en la tabla `jobs`. Si tienen `scheduled_at`, el job espera hasta esa fecha (`available_at`). El worker es el que los toma; el scheduler no dispara envíos.

```bash
php artisan queue:work
```

En otra terminal, el scheduler solo poda `failed_jobs` de más de 7 días:

```bash
php artisan schedule:work
```

Un token que FCM marca como `UNREGISTERED` / `NOT_FOUND` deja al usuario inactivo y no se reintenta. Errores 429, 500 o `UNAVAILABLE` sí, con esperas de 15s, 60s y 180s.

Para cancelar un pendiente o programado, el backoffice borra el registro de `jobs` si todavía no fue reservado. El job, si llegara a ejecutarse, vuelve a chequear el estado y no envía.

## API

Prefijo `/api/v1`. Autenticación con `X-API-Key` o `Authorization: Bearer`. Cliente inactivo o key inválida se rechaza.

Éxito:

```json
{ "success": true, "message": "...", "data": {} }
```

Error:

```json
{ "success": false, "message": "...", "errors": {} }
```

`POST /pushes` responde `202` porque el envío queda encolado. El estado se consulta con `GET /pushes/{id}`.

### Ejemplos

```bash
# Registrar o actualizar un usuario (upsert por cliente + external_user_id)
curl -X POST http://localhost:8000/api/v1/users \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: pns_demo_local_key_change_me" \
  -d '{"external_user_id":"42","firebase_token":"TOKEN","platform":"ios","name":"Ana"}'

# Actualizar solo el token
curl -X PUT http://localhost:8000/api/v1/users/42/token \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer pns_demo_local_key_change_me" \
  -d '{"firebase_token":"TOKEN_NUEVO","platform":"android"}'

# Encolar un push (scheduled_at es opcional, ISO 8601)
curl -X POST http://localhost:8000/api/v1/pushes \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: pns_demo_local_key_change_me" \
  -d '{"external_user_ids":["42"],"title":"Hola","body":"Tenés un aviso","data":{"type":"order"},"image_url":"https://example.com/a.png","scheduled_at":"2026-10-11T15:00:00-03:00"}'

# Estado
curl http://localhost:8000/api/v1/pushes/1 \
  -H "Accept: application/json" \
  -H "X-API-Key: pns_demo_local_key_change_me"
```

El cliente demo no trae service account. `POST /pushes` responde 422 hasta que cargues el JSON de Firebase en el backoffice. `registerUser` y `updateToken` no la necesitan.

La especificación está en `docs/openapi.yaml`.

## Logs

FCM HTTP v1 no envía webhooks de resultado. `firebase_requests` guarda lo que este servicio manda (OAuth y `messages:send`). `firebase_logs` guarda la respuesta HTTP de Firebase, incluidos los tokens inválidos. `api_logs` guarda cada request de la API. Tokens, API keys y credenciales se enmascaran antes de persistir.

## Tests

```bash
php artisan test
```

PHPUnit, no Pest: el plugin de Pest para Laravel 13 pide PHP 8.4 y esta app queda en 8.3.
