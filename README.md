# Yii2 Redis Connection using Sentinel

This is an extention of [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis) 
to use Sentinel as Source of Connection parameter.  
   
Based on [pyurin/yii2-redis-ha](https://github.com/pyurin/yii2-redis-ha). 
It only work on sentinel, and unable to connect to redis host without sentinel.

## Instalation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist shyevsa/yii2-redis-sentinel:"*"
```

or add

```json
"shyevsa/yii2-redis-sentinel":"*"
```

to the require section of your composer.json.

## Usage

```php
'components'=>[
    'redis' => [
        'class' => \shyevsa\redis\Connection::class,
        'master_name' => 'mymaster',
        'database' => 0
        'sentinels' => [
            '10.10.4.1',
            '10.10.4.2',
            [
              'hostname'=>'127.0.0.1',
              'port'=>3000,
            ],        
        ],        
    ]
]
```

The `sentinels` use the same connection parameter as regular `redis` connection
check [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis) for detail




