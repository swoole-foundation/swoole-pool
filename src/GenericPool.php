<?php
/**
 * @author xialeistudio
 * @date 2019-05-20
 */

namespace swoole\foundation\pool;

use Swoole\Coroutine\Channel;

/**
 * Swoole generic connection pool
 * Class Pool
 * @package swoole\foundation\pool
 */
class GenericPool
{
    /**
     * @var int pool size
     */
    private $size = 0;
    /**
     * @var callable construct a connection
     */
    private $factory = null;
    /**
     * @var Channel
     */
    private $channel = null;

    /**
     * GenericPool constructor.
     * @param int $size
     * @param callable $factory
     * @throws InvalidParamException
     */
    public function __construct($size, callable $factory)
    {
        $this->size = $size;
        $this->factory = $factory;
        $this->init();
    }


    /**
     * check pool config
     * @throws InvalidParamException
     */
    private function init()
    {
        if ($this->size <= 0) {
            throw new InvalidParamException('The "size" property must be greater than zero.');
        }
        if (empty($this->factory)) {
            throw new InvalidParamException('The "factory" property must be set.');
        }
        if (!is_callable($this->factory)) {
            throw new InvalidParamException('The "factory" property must be callable.');
        }
        $this->bootstrap();
    }

    /**
     * bootstrap pool
     */
    private function bootstrap()
    {
        $this->channel = new Channel($this->size);

        for ($i = 0; $i < $this->size; $i++) {
            $this->channel->push(call_user_func($this->factory));
        }
    }

    /**
     * Acquire a connection
     * @param int $timeout
     * @return mixed
     */
    public function acquire($timeout = 0)
    {
        return $this->channel->pop($timeout);
    }

    /**
     * Release a resource
     * @param mixed $resource
     */
    public function release($resource)
    {
        $this->channel->push($resource);
    }
}