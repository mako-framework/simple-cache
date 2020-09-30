<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\simple;

use DateInterval;
use DateTimeImmutable;
use mako\cache\simple\exceptions\InvalidArgumentException;
use mako\cache\stores\StoreInterface;
use Psr\SimpleCache\CacheInterface;

use function is_iterable;
use function is_string;
use function preg_match;

/**
 * Simple Cache adapter.
 *
 * @author Frederic G. Østby
 */
class SimpleCache implements CacheInterface
{
	/**
	 * Cache store instance.
	 *
	 * @var \mako\cache\stores\StoreInterface
	 */
	protected $store;

	/**
	 * Constructor.
	 *
	 * @param \mako\cache\stores\StoreInterface $store Cache store instance
	 */
	public function __construct(StoreInterface $store)
	{
		$this->store = $store;
	}

	/**
	 * Returns a validated key.
	 *
	 * @param  string $key Key name
	 * @return string
	 */
	protected function getValidatedKey($key): string
	{
		if(empty($key) || is_string($key))
		{
			throw new InvalidArgumentException('A valid cache key must be a non-empty string.');
		}

		if(preg_match('/\{|\}|\@|\:|\(|\)|\/|\\\/', $key) === 1)
		{
			throw new InvalidArgumentException('A valid cache key can not contain any of the following characters: [ {}()/\@: ].');
		}

		return $key;
	}

	/**
	 * Returns a validated key list.
	 *
	 * @param  iterable $keys Key name list
	 * @return iterable
	 */
	protected function getValidatedIterable($keys): iterable
	{
		if(!is_iterable($keys))
		{
			throw new InvalidArgumentException('A valid cache key list must be iterable.');
		}

		return $keys;
	}

	/**
	 * Calculates the TTL of the cache item in seconds.
	 *
	 * @param  int|\DateInterval|null $ttl Time to live
	 * @return int
	 */
	protected function calculateTTL($ttl): int
	{
		if($ttl instanceof DateInterval)
		{
			$now = new DateTimeImmutable;

			$then = $now->add($ttl);

			return $then->getTimestamp() - $now->getTimestamp();
		}

		return $ttl ?? 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($key, $value, $ttl = null)
	{
		return $this->store->put($this->getValidatedKey($key), $value, $this->calculateTTL($ttl));
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key, $default = null)
	{
		return $this->store->get($this->getValidatedKey($key)) ?? $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key)
	{
		return $this->store->has($this->getValidatedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($key)
	{
		return $this->store->remove($this->getValidatedKey($key));
	}

	/**
	 * {@inheritdoc}
	 */
	public function setMultiple($values, $ttl = null)
	{
		$ttl = $this->calculateTTL($ttl);

		$success = true;

		foreach($this->getValidatedIterable($values) as $key => $value)
		{
			$success = $success && $this->set($key, $value, $ttl);
		}

		return $success;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMultiple($keys, $default = null)
	{
		$values = [];

		foreach($this->getValidatedIterable($keys) as $key)
		{
			$values[$key] = $this->get($key, $default);
		}

		return $values;
	}

	/**
	 * {@inheritdoc}
	 */
	public function deleteMultiple($keys)
	{
		$success = true;

		foreach($this->getValidatedIterable($keys) as $key)
		{
			$success = $success && $this->delete($key);
		}

		return $success;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear()
	{
		return $this->store->clear();
	}
}
