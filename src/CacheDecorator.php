<?php namespace Iwantblv\CacheDecorator;

class CacheDecorator {

    /**
     * @var mixed
     */
    protected $cache;
    /**
     * @var int
     */
    protected $ttl = 1;
    /**
     * @var
     */
    protected $repository;

	public function __construct(CacheInterface $cache)
	{
		$this->cache = $cache;
	}

    /**
     * Set TTL
     *
     * @param $ttl
     * @return $this
     */
    public function expiresIn($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @param $repository
     * @return $this
     */
    public function decorate($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $params)
    {
        if ($this->repository)
        {
            // key factory
            $key = $this->getKey($method, $params);

            return $this->cache->get($key) ? : $this->getFresh($method, $params, $key);
        }

        throw new Exception("Repository is missing");
    }

    /**
     * Key Factory
     *
     * @param $method
     * @param $params
     * @return string
     */
    protected function getKey($method, $params)
    {
        return md5(get_class($this->repository) . $method . serialize($params));
    }

    /**
     * Gets fresh data, puts it in cache and returns it
     *
     * @param $method
     * @param $params
     * @param $newKey
     * @return mixed
     */
    protected function getFresh($method, $params, $newKey)
    {
        $fresh = call_user_func_array([$this->repository, $method], $params);

        $this->cache->put($newKey, $fresh, $this->ttl);

        return $fresh;
    }
}