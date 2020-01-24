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
