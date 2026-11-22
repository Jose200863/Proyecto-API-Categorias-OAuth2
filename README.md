# API REST de Categorías con OAuth 2.0

**ITI-621 Tecnologías y Sistemas Web III · Universidad Técnica Nacional**

API REST de categorías en PHP y MySQL, protegida con tokens de acceso, permisos por cliente y límite de solicitudes (OAuth 2.0, flujo Client Credentials).

---

## Contenido

1. [Instrucciones de ejecución](#instrucciones-de-ejecución)
2. [Clientes de prueba](#clientes-de-prueba)
3. [Uso del API](#uso-del-api)
4. [Códigos de respuesta](#códigos-de-respuesta)
5. [Flujo de una solicitud](#flujo-de-una-solicitud)
6. [Pruebas obligatorias con Postman](#pruebas-obligatorias-con-postman)
7. [Solución de problemas](#solución-de-problemas)

---

## Instrucciones de ejecución

### Requisitos

| Herramienta | Versión |
|---|---|
| PHP | 8.0 o superior |
| MySQL / MariaDB | cualquiera reciente |
| Apache | con `mod_rewrite` activo (XAMPP lo incluye) |
| Postman | para probar el API |

### Instalación

**1. Copiar el proyecto** dentro de la carpeta pública del servidor (en XAMPP, `htdocs`), en una carpeta llamada `api-categorias-oauth`.

**2. Importar la base de datos.** El script `database.sql` crea la base `ventas`, la tabla `categoria` y las tablas del proyecto: `clientes_oauth`, `tokens_oauth` y `solicitudes_api`.

```bash
mysql -u root -p < database.sql
```

También se puede importar desde phpMyAdmin con la opción **Importar**.

**3. Configurar la conexión.** Copiar la plantilla y poner los datos de MySQL:

```bash
cp configuracion/datos_conexion.example.php configuracion/datos_conexion.php
```

**4. Iniciar Apache y MySQL** desde el panel de XAMPP.

> **Importante:** `database.sql` debe importarse una sola vez. Para repetirlo, primero ejecutar `DROP DATABASE ventas;`.

---

## Clientes de prueba

| client_id | client_secret | Permisos |
|---|---|---|
| `cliente_lector` | `lector_2026` | `categorias:leer` |
| `cliente_admin` | `admin_2026` | `categorias:leer`, `categorias:escribir`, `categorias:eliminar` |

> **Nota:** son credenciales de desarrollo. En la base de datos los secretos se guardan hasheados.

---

## Uso del API

**URL base:** `http://localhost/api-categorias-oauth`

Las rutas de este documento se escriben a continuación de la URL base. Por ejemplo, `/oauth/token.php` corresponde a `http://localhost/api-categorias-oauth/oauth/token.php`. Si la carpeta del proyecto tiene otro nombre, se cambia en la URL base.

### Paso 1: obtener un token

```http
POST http://localhost/api-categorias-oauth/oauth/token.php
Authorization: Basic Auth (client_id : client_secret)
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials
```

En Postman: pestaña **Authorization**, tipo *Basic Auth*; y en **Body**, *x-www-form-urlencoded* con `grant_type = client_credentials`.

Ejemplo de respuesta:

```json
{
  "access_token": "e3b0c44298fc1c149afbf4c8996fb924...",
  "token_type": "Bearer",
  "expires_in": 900,
  "scope": "categorias:leer"
}
```

### Paso 2: consumir el API de categorías

Enviar en cada solicitud el encabezado:

```http
Authorization: Bearer <access_token>
```

| Operación | Método | URL | Cuerpo JSON | Permiso |
|---|---|---|---|---|
| Listar | GET | `/controlador/categoria.php` | - | `categorias:leer` |
| Buscar | GET | `/controlador/categoria.php?codigo=1` | - | `categorias:leer` |
| Agregar | POST | `/controlador/categoria.php` | `{"codigo": 5, "nombre": "Monitores"}` | `categorias:escribir` |
| Actualizar | PUT | `/controlador/categoria.php` | `{"codigo": 5, "nombre": "Pantallas"}` | `categorias:escribir` |
| Eliminar | DELETE | `/controlador/categoria.php` | `{"codigo": 5}` | `categorias:eliminar` |

---

## Códigos de respuesta

| Código | Significado | Cuándo ocurre |
|---|---|---|
| 200 | OK | Operación realizada o token emitido |
| 400 | Solicitud incorrecta | Solicitud incompleta o `grant_type` distinto de `client_credentials` |
| 401 | No autorizado | Credenciales inválidas, o token ausente, inválido o vencido |
| 403 | Prohibido | Token válido, pero sin el permiso requerido |
| 405 | Método no permitido | El endpoint de tokens solo acepta POST |
| 429 | Demasiadas solicitudes | Más de 10 solicitudes en 60 segundos |

> **Nota:** el token vence a los 15 minutos. El límite de solicitudes se aplica al API de categorías, no al endpoint que emite tokens.

---

## Flujo de una solicitud

```
Postman ── client_id + client_secret ──► oauth/token.php ──► access_token

Postman ── Authorization: Bearer ──────► controlador/categoria.php
                                            │
                                            ├─ valida el token y su vencimiento   → 401
                                            ├─ valida el permiso del método       → 403
                                            ├─ verifica el límite de solicitudes  → 429
                                            └─ ejecuta el CRUD de categorías      → 200
```

---

## Pruebas obligatorias con Postman

| N.º | Prueba | Resultado esperado |
|---|---|---|
| 1 | Credenciales correctas | Devuelve el token temporal y sus permisos |
| 2 | Credenciales incorrectas | Rechaza la solicitud sin emitir token |
| 3 | GET con token válido | Lista o busca categorías |
| 4 | POST, PUT y DELETE con `cliente_admin` | Realizan los cambios, verificables en MySQL |
| 5 | Sin token y token vencido | Devuelve 401 |
| 6 | `cliente_lector` intenta modificar | Devuelve 403 |
| 7 | Solicitud número 11 en 60 segundos | Devuelve 429; pasado el tiempo vuelve a permitir solicitudes |

---

## Solución de problemas

**Todas las solicitudes devuelven 401 aunque el token es correcto.**
Apache puede no estar pasando el encabezado `Authorization` a PHP. Verificar que el `.htaccess` esté en la raíz del proyecto y que `mod_rewrite` y `AllowOverride All` estén activos.

**Error 500 de conexión.**
Revisar `configuracion/datos_conexion.php` y que MySQL esté iniciado.

**No existen las tablas.**
Importar `database.sql` completo, una sola vez.