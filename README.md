# Inv-track API

Sistema para el seguimiento de inventario.

## Requisitos

Asegúrate de tener instalados los siguientes componentes en tu sistema:

- [PHP](https://www.php.net/) 8.2 o superior
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) y NPM
- [Docker](https://www.docker.com/) (para la base de datos PostgreSQL)

## Instalación y Configuración

Sigue estos pasos para configurar el proyecto en tu entorno local.

### 1. Clonar el repositorio

```bash
git clone <URL_DEL_REPOSITORIO>
cd inv-track-api
```

### 2. Iniciar la Base de Datos

El proyecto incluye un archivo `docker-compose.yml` para levantar la base de datos PostgreSQL necesaria. Asegúrate de tener Docker Desktop (o el servicio de Docker) corriendo.

```bash
docker-compose up -d
```

Esto iniciará un contenedor con PostgreSQL expuesto en el puerto definido en tu archivo `.env` (o el puerto 5432 por defecto).

### 3. Instalación Automática

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

### 4. Instalación Manual (Alternativa)

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

## Ejecución del Proyecto

Para iniciar el entorno de desarrollo local, utiliza el siguiente comando:

```bash
composer run dev
```

Este comando utilizará `concurrently` para ejecutar simultáneamente:
- El servidor de Laravel (`php artisan serve`) en `http://localhost:8000`
- El listener de colas (`php artisan queue:listen`)
- El servidor de desarrollo de Vite (`npm run dev`)
- Logs de Pail (`php artisan pail`)

## Estructura de Docker

El archivo `docker-compose.yml` levanta el siguiente servicio:

- **db**: Base de datos PostgreSQL 14.3.
  - Puertos: `5432` (o el definido en `DB_PORT`)
  - Volúmen: `postgres_data` para persistencia de datos.

Asegúrate de que las credenciales en tu archivo `.env` coincidan con las esperadas por Docker si realizas cambios manuales. Por defecto usa los valores definidos en `docker-compose.yml` o en las variables de entorno.
