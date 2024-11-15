# gloria-pickup-rfid

## Deployment
1. Copy .env.example to .env in mqtt.

## Setting up for testing
Open up 3 terminals
```sh
cd ./web/
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

```sh
npm install
npm run dev
```

```sh
cd ./live-service/
bun install
bun run build(?)
npm run generate:proto
bun run dev
```
lengkapin hehe