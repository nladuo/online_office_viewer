# online_office_viewer
preview doc/ppt/xls online via unoconv.

## Installation
``` sh
sudo apt-get install unoconv redis-server php5-redis
git clone https://github.com/nladuo/online_office_viewer.git
cd online_office_viewer && composer install
```

## Deployment
### 1. Start Redis server
``` sh
redis-server
```

### 2. Start the Convert-Worker
``` sh
cd online_office_viewer/cli
bash ./start_worker.sh
```

### 3. Start Web Server
```
cd online_office_viewer
php -S 0.0.0.0:8888
```
Now, You can visit http://localhost:8888/ and upload a office file to see result.

## LICENSE
MIT