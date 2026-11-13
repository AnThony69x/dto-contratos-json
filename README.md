# NexoCommerce – Backend API

API REST en Laravel del proyecto de aula **NexoCommerce** (IS-803, 8.º nivel A).

Contiene la **Práctica 2.2 – DTO y contratos de intercambio JSON**: registrar un pedido de compra con
`POST /api/v1/pedidos`, validando la entrada, tipándola con DTO y devolviendo un JSON con contrato fijo.

**Contenido**

1. [Inicio rápido](#1-inicio-rápido)
2. [Dónde está cada cosa](#2-dónde-está-cada-cosa)
3. [Endpoints](#3-endpoints)
4. [Cómo viaja una petición](#4-cómo-viaja-una-petición)
5. [Contrato JSON](#5-contrato-json)
6. [Pruebas](#6-pruebas)
7. [Notas y límites conocidos](#7-notas-y-límites-conocidos)

---

## 1. Inicio rápido

Requisitos: PHP 8.3+, Composer y la extensión `pdo_sqlite` (la base por defecto es SQLite).

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed     # crea tablas, el usuario id=1 y los productos id=1 e id=4
php artisan serve              # http://127.0.0.1:8000
```

Probar el endpoint: importar la colección de Postman (ver [sección 6](#postman)) o ejecutar
`php artisan test --filter=CrearPedidoTest`.

---

## 2. Dónde está cada cosa

### Práctica 2.2 (registrar pedido)

| Qué | Dónde | Para qué |
|---|---|---|
| Ruta | `routes/api.php` (bloque `v1`, al final) | `POST /api/v1/pedidos` |
| Controlador | `app/Http/Controllers/Api/V1/PedidoController.php` | `store()`: orquesta y responde 201 |
| Validación + deserialización | `app/Http/Requests/Pedidos/CrearPedidoRequest.php` | Reglas, mensajes en español y `toDto()` |
| DTO de entrada | `app/DTO/Pedidos/CrearPedidoDTO.php` | Pedido tipado e inmutable |
| DTO de ítem | `app/DTO/Pedidos/ItemPedidoDTO.php` | Línea `producto_id` + `cantidad` |
| Lógica de negocio | `app/Services/PedidoService.php` | Precios desde BD, total en centavos, transacción |
| Serialización de salida | `app/Http/Resources/PedidoResource.php` | Modelo → JSON del contrato |
| Modelos | `app/Models/Pedido.php`, `app/Models/PedidoItem.php` | Eloquent |
| Migraciones | `database/migrations/2026_11_13_000000_create_productos_table.php`<br>`database/migrations/2026_11_13_000001_create_pedidos_tables.php` | Tablas `productos`, `pedidos`, `pedido_items` |
| Datos de prueba | `database/seeders/DatabaseSeeder.php` | Usuario 1 y productos 1 y 4 |
| Pruebas automáticas | `tests/Feature/Pedidos/CrearPedidoTest.php` | P1, P1b, P2, P3 |
| Diagrama | `docs/diagrama.png` | Flujo de intercambio |
| Colección Postman | `docs/Practica_2_2_Pedidos.postman_collection.json` | P1, P2, P3 con tests |

### Resto del proyecto (de prácticas anteriores)

| Qué | Dónde |
|---|---|
| Controladores de ejemplo (datos fijos, sin BD) | `app/Http/Controllers/Api/` → `CategoriaController`, `ClienteController`, `EstadoController`, `PedidoController`, `ProductoController` |
| Usuario | `app/Models/User.php` (aún sin `HasApiTokens`: Sanctum está instalado y la tabla de tokens existe, pero no se emiten tokens) |
| Configuración de la app y manejo de errores JSON | `bootstrap/app.php` (todo `api/*` responde JSON) |

### Árbol resumido

```text
backend-api/
├── app/
│   ├── DTO/Pedidos/                 CrearPedidoDTO, ItemPedidoDTO
│   ├── Http/
│   │   ├── Controllers/Api/         controladores de ejemplo (GET)
│   │   ├── Controllers/Api/V1/      PedidoController  ← store()
│   │   ├── Requests/Pedidos/        CrearPedidoRequest
│   │   └── Resources/               PedidoResource
│   ├── Models/                      User, Pedido, PedidoItem
│   └── Services/                    PedidoService
├── database/
│   ├── migrations/                  productos, pedidos, pedido_items (2026_11_13_*)
│   └── seeders/                     DatabaseSeeder
├── docs/                            diagrama.png + colección Postman
├── routes/api.php                   todas las rutas de la API
└── tests/Feature/Pedidos/           CrearPedidoTest
```

---

## 3. Endpoints

| Método | Ruta | Estado | Descripción |
|---|---|---|---|
| `POST` | `/api/v1/pedidos` | **Real** (Práctica 2.2) | Registra un pedido en la BD |
| `GET` | `/api/estado` | Ejemplo | Estado de la API |
| `GET` | `/api/productos`, `/api/productos/{id}` | Ejemplo | Datos fijos, sin BD |
| `GET` | `/api/categorias`, `/api/categorias/{id}` | Ejemplo | Datos fijos, sin BD |
| `GET` | `/api/clientes`, `/api/clientes/{id}` | Ejemplo | Datos fijos, sin BD |
| `GET` | `/api/pedidos`, `/api/pedidos/{id}` | Ejemplo | Datos fijos, sin BD |
| `GET` | `/api/user` | Plantilla de Laravel | Protegida con `auth:sanctum`; sin login todavía no devuelve datos |

---

## 4. Cómo viaja una petición

![Diagrama de intercambio](docs/diagrama.png)

```text
JSON ─► CrearPedidoRequest ─► CrearPedidoDTO ─► PedidoService ─► BD
        (valida)               (tipa)            (precios, total,    │
                                                  transacción)       │
JSON ◄─ PedidoResource ◄───────────────── Pedido (Eloquent) ◄────────┘
        (serializa)
```

- **Deserialización:** Laravel convierte el cuerpo JSON en array; `CrearPedidoRequest` lo valida y
  `CrearPedidoDTO::fromValidated()` lo transforma en objetos tipados (`"25"` → `int 25`).
- **Serialización:** `PedidoResource` convierte el modelo en un array con nombres y tipos fijos,
  independiente de la estructura de la base de datos.
- **Decisión de diseño:** el cliente **no envía precios ni totales**. El servidor los calcula desde
  `productos` en centavos (enteros) y los devuelve como `string` decimal, para que web y móvil no puedan
  manipularlos y para evitar errores de redondeo de punto flotante.

---

## 5. Contrato JSON

### Solicitud — `POST /api/v1/pedidos`

Cabeceras: `Accept: application/json` y `Content-Type: application/json`.

| Campo | Tipo | Obligatorio | Regla |
|---|---|---|---|
| `cliente_id` | integer | Sí | Debe existir en `users` |
| `metodo_pago` | string | Sí | `efectivo` \| `tarjeta` \| `transferencia` |
| `fecha_entrega` | string `YYYY-MM-DD` | Sí | Hoy o posterior |
| `observacion` | string \| null | No | Máx. 255 caracteres |
| `items` | array | Sí | Al menos 1 |
| `items[].producto_id` | integer | Sí | Debe existir en `productos` |
| `items[].cantidad` | integer | Sí | 1 a 100 |

```json
{
  "cliente_id": 1,
  "metodo_pago": "tarjeta",
  "fecha_entrega": "2026-11-20",
  "observacion": "Entregar en la tarde",
  "items": [
    { "producto_id": 1, "cantidad": 2 },
    { "producto_id": 4, "cantidad": 1 }
  ]
}
```

### Respuesta `201 Created`

| Campo | Tipo | Nota |
|---|---|---|
| `data.pedido_id` | integer | Generado por el servidor |
| `data.estado` | string | Inicia en `pendiente` |
| `data.cliente_id`, `data.metodo_pago`, `data.fecha_entrega` | — | Lo registrado |
| `data.observacion` | string \| null | Siempre presente (`null` si no se envió) |
| `data.items[]` | array | `producto_id`, `cantidad`, `precio_unitario`, `subtotal` |
| `data.items[].precio_unitario` / `subtotal` | string decimal | 2 decimales, p. ej. `"12.50"` |
| `data.total` | string decimal | Suma de subtotales |
| `data.creado_en` | string ISO 8601 | Con zona horaria |
| `mensaje` | string | Texto informativo |

```json
{
  "data": {
    "pedido_id": 1,
    "estado": "pendiente",
    "cliente_id": 1,
    "metodo_pago": "tarjeta",
    "fecha_entrega": "2026-11-20",
    "observacion": "Entregar en la tarde",
    "items": [
      { "producto_id": 1, "cantidad": 2, "precio_unitario": "12.50", "subtotal": "25.00" },
      { "producto_id": 4, "cantidad": 1, "precio_unitario": "15.00", "subtotal": "15.00" }
    ],
    "total": "40.00",
    "creado_en": "2026-11-13T15:19:39+00:00"
  },
  "mensaje": "Pedido registrado correctamente"
}
```

### Respuesta `422 Unprocessable Entity`

Falta un campo, tipo incorrecto o regla incumplida. No se guarda nada.

```json
{
  "message": "El campo metodo_pago es obligatorio.",
  "errors": { "metodo_pago": ["El campo metodo_pago es obligatorio."] }
}
```

Si hay varios errores, `message` muestra el primero y añade `(and N more errors)` (texto de Laravel);
el detalle completo está en `errors`.

---

## 6. Pruebas

### Automáticas

```bash
php artisan test --filter=CrearPedidoTest    # solo la práctica
php artisan test                             # toda la suite
```

Usan SQLite en memoria (`phpunit.xml`), no tocan tu base de desarrollo.

| Caso | Entrada | Resultado esperado |
|---|---|---|
| P1 | JSON válido | 201 + contrato de respuesta; 1 pedido y sus ítems en BD |
| P1b | `observacion` omitida; 3 × 0.10 | `observacion: null`; total exacto `"0.30"` |
| P2 | Falta `metodo_pago` | 422 con `errors.metodo_pago`; 0 pedidos en BD |
| P3 | `cliente_id = "abc"`, `cantidad = "dos"` | 422 con `errors.cliente_id` y `errors.items.0.cantidad`; nada persistido |

### Postman

1. Tener el servidor corriendo (`php artisan serve`) y la BD sembrada (`php artisan migrate --seed`).
2. En Postman: **Import** → `docs/Practica_2_2_Pedidos.postman_collection.json`.
3. Variables de la colección: `base_url` = `http://127.0.0.1:8000`, `cliente_id` = `1`.
4. Ejecutar cada petición o la colección completa con **Run**.

`fecha_entrega` se calcula sola (mañana), así que la regla «hoy o posterior» no falla según el día.
Cada petición incluye sus tests (código HTTP y estructura del contrato).

---

## 7. Notas y límites conocidos

- **Sin autenticación en el endpoint:** `CrearPedidoRequest::authorize()` devuelve `true` y `cliente_id`
  viene en el cuerpo. Para producción, proteger la ruta con `auth:sanctum` y tomar el cliente del usuario
  autenticado.
- **Zona horaria:** `creado_en` sale en UTC (`+00:00`) porque `config/app.php` usa `UTC`.
- **Los controladores de ejemplo** (`/api/productos`, `/api/pedidos`…) devuelven datos fijos y **no leen** la
  tabla `productos` ni `pedidos`; el registro real de pedidos solo existe en `POST /api/v1/pedidos`.
- **Catálogo mínimo:** la tabla `productos` solo tiene `nombre` y `precio`; `DatabaseSeeder` inserta 2
  productos de prueba (ids 1 y 4). Ejecutarlo dos veces falla por ids duplicados: usar `php artisan migrate:fresh --seed`.
