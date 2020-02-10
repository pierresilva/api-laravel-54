# RestAPI Aplicación Freeradius
﻿
1. instalar dependencias `composer install`
2. Copiar archvo de configuración `cp .env.example .env`
3. Definir base de datos. Modificar en el archivo `.env`
```dotenv
   DB_CONNECTION=MySQL
   DB_HOST=127.0.0.1 
   DB_PORT=3306
   DB_DATABASE=api-laravel-54
   DB_USERNAME=homestead
   DB_PASSWORD=secret
```
4. Crear tablas `php artisan migrate`
5. Editar en el software bamboo `hotel5/RadiusConfig.php` segun el servidor freeradius a usar:
```php
<?php
    $radius_config = [
        'radius_api' => 'http://server/public/index.php', // IP, ruta o URL del servidor de aplicación freeradius
        'radius_db_host' => '172.16.32.133', // Dirección IP de la base de datos del servidor freeradius
        'radius_db_database' => 'radius' // Nombre de la base de datos del servidor freeradius
     ];
```

## Configuración de servidores freeradius

### Crear conexión a servidor freeradius

`POST` http://server/api/databases

#### Request (ejemplo)

```json
{
    "driver": "mysql",
    "host": "192.168.0.29",
    "port": "3306",
    "database": "radius",
    "username": "radiu",
    "password": "radpass"
}
```

#### Response  (ejemplo)

```json
{
    "message": "OK",
    "data": {
        "driver": "mysql",
        "host": "192.168.0.29",
        "port": "3306",
        "database": "radius",
        "username": "radiu",
        "password": "radpass",
        "updated_at": "2020-01-16 13:32:32",
        "created_at": "2020-01-16 13:32:32",
        "id": 2
    }
}
```

### Actualizar conexión a servidor freeradius

`PUT` http://server/api/databases/{id}

#### Request (ejemplo)

```json
{
	"driver": "mysql",
	"host": "172.16.32.133",
	"port": "3306",
	"database": "radius",
	"username": "radius",
	"password": "radpass"
}
```

#### Response  (ejemplo)

```json
{
    "message": "OK",
    "data": {
        "id": 1,
        "driver": "mysql",
        "host": "172.16.32.133",
        "port": "3306",
        "database": "radius",
        "username": "radius",
        "password": "radpass",
        "created_at": "2020-01-16 00:00:00",
        "updated_at": "2020-01-16 00:00:00"
    }
}
```

### Obtener conexiones de servidores freeradius

`GET` http://server/api/databases

#### Request (ejemplo)

```
NULL
```

#### Response  (ejemplo)

```json
{
    "message": "OK",
    "data": [
        {
            "id": 1,
            "driver": "mysql",
            "host": "172.16.32.133",
            "port": "3306",
            "database": "radius",
            "username": "radius",
            "password": "radpass",
            "created_at": "2020-01-16 00:00:00",
            "updated_at": "2020-01-16 00:00:00"
        }
    ]
}
```

## Configuración de los usuarios freeradius

### Obtener usuarios freeradius

`GET` http://server/api/radius/users

#### Request (ejemplo)

```json
{
	"host": "172.16.32.133",
	"database": "radius"
}
```

#### Response  (ejemplo)

```json
{
    "message": "OK",
    "data": [
        {
            "id": 1,
            "username": "test_user",
            "attribute": "User-Password",
            "op": ":=",
            "value": "password"
        },
        {
            "id": 18,
            "username": "someuser2",
            "attribute": "User-Password",
            "op": "==",
            "value": "12345"
        },
        {
            "id": 20,
            "username": "112",
            "attribute": "User-Password",
            "op": "==",
            "value": "nZqIy0jkFN"
        }
    ]
}
```

### Obtener usuario freeradius

`GET` http://server/api/radius/users/{username}

#### Request (ejemplo)

```json
{
	"host": "172.16.32.133",
	"database": "radius"
}
```

#### Response  (ejemplo)

```json
{
    "message": "OK",
    "data": {
        "id": 18,
        "username": "username",
        "attribute": "User-Password",
        "op": "==",
        "value": "12345"
    }
}
```


### Crear usuarios freeradius

`POST` http://server/api/radius/users

#### Request (ejemplo)

```json
{
	"host": "172.16.32.133",
	"database": "radius",
	"username": "someuser",
	"value": "12345"
}
```

#### Response  (ejemplo)

```json
{
    "message": "OK",
    "data": {
        "username": "someuser",
        "attribute": "User-Password",
        "value": "12345",
        "id": 18
    }
}
```

### Eliminar usuarios freeradius

`DELETE` http://server/api/radius/users/{username}

#### Request (ejemplo)

```json
{
	"host": "172.16.32.133",
	"database": "radius"
}
```

#### Response  (ejemplo)

```json
{
    "message": "Usuario eliminado!"
}
```
# Rest API Aplicación Reservas por Central de Reservas
Agregar el campo `metadata` a la tabla `reserva`
```sql
ALTER TABLE reserva ADD metadata LONGTEXT NULL;
```
Agregar el campo `valornoche` a la tabla `plares`
```sql
ALTER TABLE plares ADD valornoche INTEGER NULL;
```

Configurar la conexión `hhotel5` en `config/database.php` apuntando a la base de datos de bamboo. *Ejemplo*:
```php
'hhotel5' => [
    'driver' => 'mysql',
    'host' => '192.168.0.17',
    'port' => '3306',
    'database' => 'hhotel5',
    'username' => 'root',
    'password' => 'hea101',
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
],
```

### CM Reservas

Configurar en Bamboo los datos del WebService en `hotel5\app\clases\ReservationsChannel.php`. *Ejemplo*:

```php
public function __construct()
{
    $this->apiUrl = 'http://api-laravel-54.test';
    $this->hotelId = '3885';
    $this->hotelName = '3885';
    $this->bookingEngineCode = 'cm-reservas';
}
```

Configurar los datos del WebService CM Reservas en `config/cm_reservas`. *Ejemplo*:
```php
return [
    'url' => 'https://apitest.roomcloud.net',
    'apyKey' => 'bamboo_900hty5768fj5o6msds4',
    'hotel_id' => 3885,
    'userName' => '3885',
    'password' => 'Bamboo2019',
    'action' => '/be/search/xml.jsp',
    'default_rate' => '766', // id del cargo a aplicar
    'rooms_cl' => [ // habitaciones id de cm reservas, id de clase bamboo
        '17661' => '15',
        '17662' => '14',
    ],
    'rooms_lc' => [ // habitaciones id de clase bamboo, id de cm reservas
        '15' => '17661',
        '14' => '17662',
    ],
    'paymentType' => '46', // Código del tipo de pago a aplicar
    'warrantyType' => '2', // Código del tipo de garantia a aplicar
    'programType' => '7', // Código del tipo de programa a aplicar
    'codpla' => 728, // Código del plan a aplicar
];
```

## Comandos

**Obtener Reservas:**

`php artisan cr:get_reservation cm-reservas`

Obtiene las reservas generadas en los canales de reservas y las almacena en bamboo.

**Actuaizar inventario**

`php artisan cr:put_inventory 2020-03-15 2020-03-18 1 cm-reservas`

Actualiza el inventario en el canal de reservas desde una fecha inicial hasta una fecha final por el código de la clase de habitación

##Tareas programadas
Configurar la tarea programada (cronjob):

`* * * * * php /ruta-a-este-proyecto/artisan schedule:run >> /dev/null 2>&1`

Esto ejecutara los trabajos programados. [Ver Documentación Laravel: Task Scheduling](https://laravel.com/docs/5.4/scheduling#introduction)
