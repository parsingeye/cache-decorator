<?php namespace Iwantblv\CacheDecorator;

class CacheDecorator {

	protected $cache;

	protected $cacheNamepace = '';

	protected $life = 1;

	protected $repository;

	public function __construct(CacheInterface $cache)
	{
		$this->cache = $cache;
	}

	public function decorate($repository)
	{
		$this->repository = $repository;

		return $this;
	}

	public function __call($method, $params)
	{
		$key = $this->getKey($method, $params);

		return $this->cache->get($key) ?: $this->getFresh($key, $method, $params);
	}

	protected function getKey($method, $params)
	{
		return sprintf('%s.%s', $this->cacheNamepace, md5($method . implode('', $params)));
	}

	protected function getFresh($key, $method, $params)
	{
		if ($this->repository)
		{
			$fresh = call_user_func([$this->repository, $method], $params);

			$this->cache->put($key, $fresh, $this->life);

			return $fresh;
		}

		return throw new Exception("Repository is missing");
	}
}