# API REST de Categorías con OAuth 2.0

**ITI-621 Tecnologías y Sistemas Web III · Universidad Técnica Nacional**

API REST de categorías en PHP y MySQL, protegida con OAuth 2.0 (flujo Client Credentials).

---

## Instrucciones de ejecución

### Requisitos

| Herramienta | Versión |
|---|---|
| PHP | 8.0 o superior |
| MySQL / MariaDB | Cualquier versión reciente |
| Apache | Con `mod_rewrite` activo (XAMPP lo incluye) |
| Postman | Para probar el API |

### Instalación

**Paso 1. Copiar el proyecto**

Colocarlo en la carpeta pública del servidor (en XAMPP, `htdocs`), dentro de una carpeta llamada `Proyecto-API-Categorias-OAuth2`.

**Paso 2. Importar la base de datos**

En phpMyAdmin, pestaña **Importar**, seleccionar el archivo `database/Database.sql`.

El script crea la base `ventas`, la tabla `categoria` y las tablas del proyecto: `clientes_oauth`, `tokens_oauth` y `solicitudes_api`.

> **Importante:** el script debe importarse una sola vez.

**Paso 3. Configurar la conexión**

1. Copiar `configuracion/DatosConexionExample.php`.
2. Renombrar la copia como `configuracion/DatosConexion.php`.
3. Poner en ella los datos de MySQL.

**Paso 4. Iniciar los servicios**

Iniciar Apache y MySQL desde el panel de XAMPP.

---

## Clientes de prueba

| client_id | client_secret | Permisos |
|---|---|---|
| `cliente_lector` | `lector_2026` | `categorias:leer` |
| `cliente_admin` | `admin_2026` | `categorias:leer`, `categorias:escribir`, `categorias:eliminar` |

---

## Uso del API

**URL base:** `http://localhost/Proyecto-API-Categorias-OAuth2`

### 1. Obtener un token

```http
POST /oauth/token.php
Authorization: Basic Auth (client_id y client_secret)
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials
```

En Postman: pestaña **Authorization**, tipo *Basic Auth*; y en **Body**, opción *x-www-form-urlencoded* con `grant_type` igual a `client_credentials`.

Respuesta:

```json
{
  "access_token": "...",
  "token_type": "Bearer",
  "expires_in": 900,
  "scope": "categorias:leer"
}
```

### 2. Consumir el API de categorías

Enviar en cada solicitud el encabezado:

```http
Authorization: Bearer <access_token>
```

| Operación | Método | URL | Cuerpo JSON | Permiso |
|---|---|---|---|---|
| Listar | `GET` | `/controlador/Categoria.php` | - | `categorias:leer` |
| Buscar | `GET` | `/controlador/Categoria.php?codigo=1` | - | `categorias:leer` |
| Agregar | `POST` | `/controlador/Categoria.php` | `{"codigo": 5, "nombre": "Monitores"}` | `categorias:escribir` |
| Actualizar | `PUT` | `/controlador/Categoria.php` | `{"codigo": 5, "nombre": "Pantallas"}` | `categorias:escribir` |
| Eliminar | `DELETE` | `/controlador/Categoria.php` | `{"codigo": 5}` | `categorias:eliminar` |

---

## Respuestas de error

| Código | Cuándo ocurre |
|---|---|
| **401** | Token ausente, inválido o vencido |
| **403** | El cliente no tiene el permiso requerido |
| **429** | Más de 10 solicitudes en 60 segundos |