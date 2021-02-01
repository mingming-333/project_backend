**還原 vendor**
```sh
composer install
```

**還原 node_modules**
```sh
npm install
```

**還原.env**
```sh
cp .env.example .env
php artisan key:generate
```

**修改.env**
```
DB_DATABASE=*你的資料庫名稱（自行建立）*
DB_USERNAME=*使用者帳號*
DB_PASSWORD=*使用者密碼*
```

**重建資料庫**
```sh
php artisan migrate --seed
```

**Passport install**
```sh
php artisan passport:install
```

**JWT secret**
```sh
php artisan jwt:secret
```
