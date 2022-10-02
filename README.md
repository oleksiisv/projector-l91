# projector-l91

## Installation

Clone the repository.
Run ``` docker-compose up --scale redis-sentinel=3 -d``` command to launch application. This will launch all required containers inclusing 3 redis sentinels. 

After running container run ```docker inspect src-redis-1``` to get redis master IP and set it's IP in **src/redis/redis-sentinel/conf/sentinel.conf** 172.21.0.4. Restart sentinel containers: \
```docker container restart src-redis-sentinel-1 src-redis-sentinel-2 src-redis-sentinel-3```

Please note: not all redis sentinels will launch after docker-compose due to the error: 
>Error response from daemon: Ports are not available: exposing port TCP 0.0.0.0:26380 -> 0.0.0.0:0: listen tcp 0.0.0.0:26380: bind: address already in use

you may need to start containers manually. 

Docker exec to the laravel application container, run:
* ```php artisan migrate``` to create database tables
* ```php artisan db:seed --class=TransactionSeeder ```




