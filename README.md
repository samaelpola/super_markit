# SUPER MARKIT

Creating an ecommerce site with symfony framework

To run this application, you must have docker installed on your machine

To test the connection with google you need to provide me with your email address so that I can add it to the test
accounts

#

## Test credit card

use any combination of three digits for the cvc and for the expiry date use a date that has not yet passed

#### payment success

```
4242 4242 4242 4242
```

#### payment fail

```
4000 0000 0000 9995
```

#

## Getting Started

### Launch app

```
make
```

### after launch app run

```
make asset
```

```
make migration
```

Open http://localhost:8000 with your browser to see the result.

link useful:

phpmyadmin -> http://localhost:8080

minio -> http://localhost:9001

redis -> http://localhost:8081

#

## Command useful

### Build project

```
make build
```

### Install all dependencies

```
 make install
```

### Up project

```
make up
```

### Down container

```
make down
```

### Executing command inside the container

```
make exec
```

### Database

#### execute migration

```
make migration
```

### Asset

#### compile asset

```
make asset
```
