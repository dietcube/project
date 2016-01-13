Project skeleton for Dietcube
==============================

Setup
-----

```
composer install
```

Configuration File
------------------

```
edit app/config/config.php
edit app/config/config_{DIET_ENV}.php
```

Set debug mode on

```
<?php

return [
    'debug' => true,

    ...
];
```

Environment
-----------

* `DIET_ENV`
    * `production`: production mode
    * `development`: development mode

Apache Conf:

```
SetEnv DIET_ENV production
```

Nginx Conf (with php-fpm):

```
fastcgi_param  DIET_ENV development;
```

### Run with PHP built-in server

```
DIET_ENV=development php -d variables_order=EGPCS -S localhost:8080 -t webroot/
```
