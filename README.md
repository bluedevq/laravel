## Requirement
```
- Laravel 10.3
- Apache 2.4.x 
- PHP 8.1.x
- MySQL 8.x
- Composer 2.x
```

## Install
```
./scripts/install.sh
```

Permission
```
sudo usermod -a -G apache ec2-user
sudo chown -R ec2-user:apache /var/www/html
```

Clear cache and restart queue
```
sudo chmod -R 777 bootstrap/cache
sudo chmod -R 777 storage
sudo -u apache php artisan optimize:clear
```

## Command
Backup logs
```
sudo -u apache php artisan log:backup
sudo -u apache php artisan log:backup month
```

Backup database (mysql, postgresql)
```
sudo -u apache php artisan db:backup
```

## OpenAPI

Generating OpenAPI documents
```
php artisan openapi:generate >| public/documents/api.json
```

Create an api
```
php artisan make:api-generate Api/User
php artisan make:factory UserFactory
php artisan make:test Http/Controllers/Api/GetUserTest
```

## Pint

Check convention
```
./vendor/bin/pint --config config/pint.json -v
```

Fix convention
```
./vendor/bin/pint --config config/pint.json
```

## Pest test

Create feature test
```
php artisan make:test Api/UserTest
```

Create unit test
```
php artisan make:test Api/UserTest --unit
```

Test all
```
./vendor/bin/pest
```

Test specific class
```
./vendor/bin/pest --filter Api/UserTest
```

Test with coverage
```
./vendor/bin/pest --coverage-html public/reports
```
