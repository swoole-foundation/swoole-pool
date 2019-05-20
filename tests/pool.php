<?php
/**
 * @author xialeistudio
 * @date 2019-05-20
 */

use Swoole\Coroutine\MySQL;
use swoole\foundation\pool\GenericPool;

require __DIR__ . '/../vendor/autoload.php';


go(function () {
    $pool = new GenericPool(10, function () use (&$count) {
        $connection = new MySQL();
        $connection->connect([
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'password' => 'root',
            'database' => 'blog',
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
});

swoole_event_wait();