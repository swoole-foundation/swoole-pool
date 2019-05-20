# swoole pool
基于channel的swoole通用协程池

## 快速开始

需要在协程函数环境下运行以下代码

```php
<?php
$pool = new GenericPool(10, function () {
        $connection = new MySQL();
        $connection->connect([
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => 'root',
            'database' => 'test',
        ]);
        if (!$connection->connected) {
            throw new Exception($connection->connect_error, $connection->errno);
        }
        return $connection;
    });


for ($i = 0; $i < 100; $i++) {
    go(function () use ($pool, $i) {
        /** @var MySQL $connection */
        $connection = $pool->acquire();
        defer(function () use ($pool, $connection) {
            $pool->release($connection);
        });

        $data = $connection->query('SELECT CONNECTION_ID() AS `id`');
        print_r($data);
    });
}
```