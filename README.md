# Inv-track API

Sistema para el seguimiento de inventario.

## Requisitos

Asegúrate de tener instalados los siguientes componentes en tu sistema:

- [PHP](https://www.php.net/) 8.2 o superior
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) y NPM
- [Docker](https://www.docker.com/) y Docker Compose

## Instalación y Configuración

Sigue estos pasos para configurar el proyecto en tu entorno local.

### 1. Clonar el repositorio

```bash
git clone <URL_DEL_REPOSITORIO>
cd inv-track-api
```

### 2. Configurar variables de entorno

```bash
cp .env.example .env
```

Asegúrate de que las variables de base de datos sean coherentes con Docker:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=postgres
DB_PASSWORD=password
```

### 3. Instalación Automática (sin Docker para la app)

El proyecto cuenta con un script automatizado para instalar dependencias y configurar el entorno. Una vez que la base de datos esté corriendo, ejecuta:

```bash
composer run setup
```

Este comando realizará las siguientes acciones:
- Instalará las dependencias de PHP (Composer).
- Creará el archivo `.env` si no existe.
- Generará la clave de la aplicación (`APP_KEY`).
- Ejecutará las migraciones de base de datos.
- Instalará las dependencias de Node (NPM).
- Compilará los archivos del frontend.

### 4. Instalación Manual (Alternativa sin Docker para la app)

Si prefieres ejecutar los pasos uno a uno:

```bash
# Instalar dependencias de PHP
composer install

# Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# Ejecutar migraciones (La base de datos debe estar corriendo)
php artisan migrate

# Instalar dependencias de Frontend y construir assets
npm install
npm run build
```

## Levantar con Docker (Desarrollo)

Usa `compose.dev.yaml` para correr app + nginx + postgres en modo desarrollo.

```bash
docker compose -f compose.dev.yaml up -d --build
```

Servicios en desarrollo:
- App Laravel (PHP-FPM): `app`
- Nginx: `http://localhost:8000`
- PostgreSQL: `localhost:5432`

Comandos útiles:

```bash
# Ver estado
docker compose -f compose.dev.yaml ps

# Ver logs
docker compose -f compose.dev.yaml logs -f

# Ejecutar migraciones
docker compose -f compose.dev.yaml exec app php artisan migrate

# Detener entorno
docker compose -f compose.dev.yaml down
```

## Levantar con Docker (Producción)

Usa `compose.prod.yaml` para correr app + nginx + postgres en configuración de producción.

```bash
docker compose -f compose.prod.yaml up -d --build
```

Puertos por defecto en producción:
- HTTP: `80`
- HTTPS: `443`

Puedes cambiarlos con variables:

```env
APP_PORT=80
APP_SSL_PORT=443
```

Comandos útiles:

```bash
# Ver estado
docker compose -f compose.prod.yaml ps

# Ver logs
docker compose -f compose.prod.yaml logs -f

# Detener entorno
docker compose -f compose.prod.yaml down
```
