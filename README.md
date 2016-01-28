Project skeleton for Dietcube
==============================

Setup
-----

This package is a project skeleton for Dietcube.

```
composer create-project dietcube/project -s dev your-project
```

(`your-project` is a sample directory name for the project. Camelized name of the directory is used as your application namespace (e.g. `YourProject\\`).


Configuration File
------------------

```
edit app/config/config.php
edit app/config/config_{DIET_ENV}.php
```

Set debug mode on:

```
<?php

return [
    'debug' => true,

    ...
];
```

Environment
-----------

`DIET_ENV` is the ENV name.

If `DIET_ENV` is not set for any environment variable (Dietcube checks `$_SERVER['DIET_ENV']` and `getenv('DIET_ENV')`), `Dispatcher::getEnv()` returns `production` by default.

Typically, `development` is used for development environment so `dietcube-project`'s initialise script generates `app/config/config_development.php` for default development config file.

### Example: Configuration of Web Server

For example, set `DIET_ENV` as `development`.

Apache Conf:

```
SetEnv DIET_ENV production
```

Nginx Conf (with php-fpm):

```
fastcgi_param  DIET_ENV production;
```

### Run with PHP built-in server

```
DIET_ENV=development php -d variables_order=EGPCS -S 0:8080 -t webroot/
```
