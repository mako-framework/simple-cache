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

use function array_keys;
use function is_array;
use function is_iterable;
use function is_string;
use function iterator_to_array;
use function preg_match;

/**
 * Simple Cache adapter.
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
		if(empty($key) || !is_string($key))
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
	 * @return array
	 */
	protected function getValidatedKeys(iterable $keys): array
	{
		$validatedKeys = [];

		foreach($keys as $key)
		{
			$validatedKeys[] = $this->getValidatedKey($key);
		}

		return $validatedKeys;
	}

	/**
	 * Calculates the TTL of the cache item in seconds.
	 *
	 * @param  \DateInterval|int|null $ttl Time to live
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
	 * {@inheritDoc}
	 */
	public function set($key, $value, $ttl = null)
	{
		return $this->store->put($this->getValidatedKey($key), $value, $this->calculateTTL($ttl));
	}

	/**
	 * {@inheritDoc}
	 */
	public function get($key, $default = null)
	{
		return $this->store->get($this->getValidatedKey($key)) ?? $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function has($key)
	{
		return $this->store->has($this->getValidatedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function delete($key)
	{
		return $this->store->remove($this->getValidatedKey($key));
	}

	/**
	 * {@inheritDoc}
	 */
	public function setMultiple($values, $ttl = null)
	{
		if(!is_iterable($values))
		{
			throw new InvalidArgumentException('The list of values must be iterable.');
		}

		if(!is_array($values))
		{
			$values = iterator_to_array($values);
		}

		$ttl = $this->calculateTTL($ttl);

		$success = true;

		foreach($this->getValidatedKeys(array_keys($values)) as $key)
		{
			$success = $success && $this->store->put($key, $values[$key], $ttl);
		}

		return $success;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMultiple($keys, $default = null)
	{
		if(!is_iterable($keys))
		{
			throw new InvalidArgumentException('A valid cache key list must be iterable.');
		}

		$values = [];

		foreach($this->getValidatedKeys($keys) as $key)
		{
			$values[$key] = $this->store->get($key) ?? $default;
		}

		return $values;
	}

	/**
	 * {@inheritDoc}
	 */
	public function deleteMultiple($keys)
	{
		if(!is_iterable($keys))
		{
			throw new InvalidArgumentException('A valid cache key list must be iterable.');
		}

		$success = true;

		foreach($this->getValidatedKeys($keys) as $key)
		{
			$success = $success && $this->store->remove($key);
		}

		return $success;
	}

	/**
	 * {@inheritDoc}
	 */
	public function clear()
	{
		return $this->store->clear();
	}
}
