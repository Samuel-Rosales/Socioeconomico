# Proyecto Formulario Socioeconómico

Proyecto de formulario web desarrollado con PHP 7.1, Tailwind CSS y arquitectura MVC para consumir APIs externas.

## Estructura del Proyecto (MVC)

```
FRONTEND-SOCIOECONOMICO/
├── app/
│   ├── controllers/         # Controladores (lógica de negocio)
│   │   └── FormController.php
│   ├── models/             # Modelos (representación de datos)
│   │   └── FormData.php
│   ├── services/           # Servicios para consumir APIs
│   │   └── ApiService.php
│   └── views/              # Vistas (presentación)
│       ├── layouts/
│       │   └── main.php
│       ├── form/
│       │   ├── index.php
│       │   └── success.php
│       └── errors/
│           └── 404.php
├── config/
│   ├── config.php          # Configuración general
│   └── routes.php          # Definición de rutas
├── core/
│   ├── Autoloader.php      # Autoloader PSR-4
│   ├── Controller.php      # Clase base para controladores
│   └── Router.php          # Sistema de enrutamiento
├── public/                 # Directorio público (document root)
│   ├── index.php          # Front controller
│   ├── .htaccess          # Configuración Apache
│   └── public/assets/
│       └── css/
│           └── output.css  # CSS compilado
├── src/
│   └── input.css          # CSS fuente de Tailwind
├── node_modules/
├── package.json
├── tailwind.config.js
└── README.md
```

## Requisitos

- PHP 7.1 o superior
- Apache con mod_rewrite habilitado
- Node.js y npm (para compilar Tailwind CSS)
- Navegador web moderno

## Instalación de PHP 7.1

### Windows

1. **Opción 1: XAMPP (Recomendado para desarrollo)**
   - Descargar XAMPP desde: https://www.apachefriends.org/download.html
   - Buscar una versión que incluya PHP 7.1.x
   - Instalar y ejecutar el panel de control de XAMPP
   - Activar Apache
   - **Importante**: Configurar el document root a la carpeta `public/`

2. **Opción 2: WAMP**
   - Descargar WAMP desde: https://www.wampserver.com/en/
   - Buscar una versión con PHP 7.1
   - Configurar virtual host apuntando a `public/`

### Verificar instalación

```bash
php -v
```

## Configuración

### 1. Instalar dependencias de Node.js

```bash
npm install
```

### 2. Configurar la aplicación

Editar `config/config.php`:

```php
// Configuración de API
define('API_BASE_URL', 'https://tu-api.com/v1');
define('API_TOKEN', 'tu-token-aqui'); // Si es necesario
```

### 3. Compilar CSS

```bash
npm run build:css
```

### 4. Configurar Apache

El document root debe apuntar a la carpeta `public/`:

**XAMPP**: Editar `httpd.conf` o crear un virtual host
**WAMP**: Configurar en el panel de control

Ejemplo de virtual host:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/dev/FRONTEND-SOCIOECONOMICO/public"
    ServerName formulario.local
    
    <Directory "C:/dev/FRONTEND-SOCIOECONOMICO/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Arquitectura MVC

### Controladores (`app/controllers/`)

Manejan la lógica de negocio y coordinan entre modelos y vistas.

```php
namespace App\Controllers;
use Core\Controller;

class FormController extends Controller {
    public function index() {
        $this->view('form/index');
    }
}
```

### Modelos (`app/models/`)

Representan y validan los datos.

```php
$formData = new FormData($_POST);
if ($formData->validate()) {
    // Datos válidos
}
```

### Servicios (`app/services/`)

Consumen APIs externas.

```php
$api = new ApiService();
$response = $api->post('/endpoint', $data);
```

### Vistas (`app/views/`)

Presentan la información al usuario. Solo HTML y PHP de presentación.

### Rutas (`config/routes.php`)

Definen el mapeo de URLs a controladores:

```php
$router->get('/', 'FormController@index');
$router->post('/submit', 'FormController@submit');
```

## Tailwind CSS

### Scripts disponibles

- **`npm run build:css`** - Compila el CSS una vez
- **`npm run watch:css`** - Compila y observa cambios (modo desarrollo)
- **`npm run build:prod`** - Compila y minifica para producción

### Componentes personalizados

Definidos en `src/input.css`:

- `.btn-primary` - Botón principal
- `.input-field` - Campo de entrada
- `.label-field` - Etiqueta de formulario
- `.card` - Tarjeta con sombra

### Desarrollo

```bash
npm run watch:css
```

Mantén este comando ejecutándose mientras desarrollas.

## Uso

1. Iniciar Apache (XAMPP/WAMP)
2. Ejecutar `npm run watch:css` en una terminal
3. Acceder a `http://localhost/` o tu virtual host
4. El formulario estará disponible en la página principal

## Consumir APIs

### Configurar endpoint

En `config/config.php`:

```php
define('API_BASE_URL', 'https://api.ejemplo.com/v1');
```

### Usar en controlador

```php
$api = new ApiService();
$response = $api->post('/formulario', $data);

if ($response['success']) {
    // Éxito
} else {
    // Error
}
```
