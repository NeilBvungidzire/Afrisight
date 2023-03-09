# Members Web App

## Release notes

* Run `docker-compose -f docker-compose.dev.yml up --build -d` to rebuild php image so gmp extension can be installed.
* Run `composer install` in php container to install ext-gmp package.
* Run database migrations `php artisan migrate`.
* Clean cache `php artisan cache:clear`.

## Docker/Laradock configurations

* Copy folders from `laradock` directory into laradock folder, which will be used to run the environment.
* Setup environment variables for application and laradock.

*Nginx proxy*
* Set domain name with `WEBSERVER_DOMAIN_NAME`.

*Laradock*
* Set application directory with `APP_CODE_PATH_HOST`.
* Set host data directory with `DATA_PATH_HOST`.
* set project prefix for docker container names with `COMPOSE_PROJECT_NAME`.
* Create DB for telescope named "telescope".

*PHP extensions*
* PHP-GMP: `WORKSPACE_INSTALL_GMP=true`, `PHP_FPM_INSTALL_GMP=true`, `PHP_WORKER_INSTALL_GMP=true`.
* ZIP: `PHP_WORKER_INSTALL_ZIP_ARCHIVE=true`. When running Supervisor, Composer dependencies require this PHP extensions to be installed.
* GD: `PHP_WORKER_INSTALL_GD`. When running Supervisor, Composer dependencies require this PHP extensions to be installed.

*PHP INI*
* Set memory limit in `php[VERSION].ini` file with `memory_limit` parameter.

*Lets Encrypt*
* In case Lets Encrypt can't generate the certificates, add self-signed certificates (crt- and key-files) into `/certs` directory named like `WEBSERVER_DOMAIN_NAME`, so for example `mywebsite.com.crt` and `mywebsite.com.key`. This is needed for Lets Encrypt to generate the certificates.

*Dependencies*
* Log into workspace and install Composer and NPM dependencies.

*Php-worker*
* On container creation you need to use certain php image version, which includes certain [fix](https://github.com/laradock/laradock/issues/3013#issuecomment-874129985).

## Database dump

- Export dump of specific database and only data
```shell script
docker exec -i [CONTAINER_NAME] sh -c 'mysqldump --user="root" --password="[PASSWORD]" --skip-triggers --compact --no-create-info "[DATABASE_NAME]"' > ./dumps/[FILE_NAME].sql
```

- Import dump of specific database
```shell script
docker exec -i [CONTAINER_NAME] sh -c 'mysql --user="root" --password="[PASSWORD]" "[DATABASE_NAME]"' < [PATH_DUMP_FILE]
```

## Transfer file (e.g. dump) from server to local
```shell script
scp devop@[HOST]:[PATH_FROM] [DIR_TO]
```

## Know-hows
* Enter workspace container: `docker-compose exec --user=laradock workspace bash`.
* Start containers: `docker-compose up -d nginx mariadb redis php-worker`.
* When changing a queue-able job, make sure to run `php artisan queue:restart`, since queue workers are long-lived processes (https://laravel.com/docs/5.8/queues#queue-workers-and-deployment).
* When changing a scheduled job, make sure to restart the worker `docker-compose restart php-worker`.

## IMPORTANT
* After new or updated job, always run `php artisan queue:restart` to make it work.
