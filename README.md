# project2PSF

Creating an ecommerce site with symfony framework

To run this application, you must have docker installed on your machine

To test the connection with google you need to provide me with your email address so that I can add it to the test accounts

### Test credit card 
use any combination of three digits for the cvc and for the expiry date use a date that has not yet passed

#### payment success
```
4242 4242 4242 4242
```
#### payment fail
```
4000 0000 0000 9995
```


### Launch app

```
make
```

#### after launch app
```
make asset
```


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
