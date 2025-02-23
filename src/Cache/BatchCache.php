<?php

namespace Analize\Excel\Cache;

use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\CacheInterface;

class BatchCache implements CacheInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var MemoryCache
     */
    protected $memory;

    /**
     * @var null|int|\DateInterval|callable
     */
    protected $defaultTTL = null;

    /**
     * @param  CacheInterface  $cache
     * @param  MemoryCache  $memory
     * @param  null|int|\DateInterval|callable  $defaultTTL
     */
    public function __construct(
        CacheInterface $cache,
        MemoryCache $memory,
        null|int|\DateInterval|callable $defaultTTL = null
    ) {
        $this->cache      = $cache;
        $this->memory     = $memory;
        $this->defaultTTL = $defaultTTL;
    }

    public function __sleep()
    {
        return ['memory'];
    }

    public function __wakeup()
    {
        $this->cache = Cache::driver(
            config('excelAnalize.cache.illuminate.store')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->memory->has($key)) {
            return $this->memory->get($key);
        }

        return $this->cache->get($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        if (func_num_args() === 2) {
            $ttl = value($this->defaultTTL);
        }

        $this->memory->set($key, $value, $ttl);

        if ($this->memory->reachedMemoryLimit()) {
            return $this->cache->setMultiple($this->memory->flush(), $ttl);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        if ($this->memory->has($key)) {
            return $this->memory->delete($key);
        }

        return $this->cache->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $this->memory->clear();

        return $this->cache->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        // Check if all keys are still in memory
        $memory              = $this->memory->getMultiple($keys, $default);
        $actualItemsInMemory = count(array_filter($memory));

        if ($actualItemsInMemory === count($keys)) {
            return $memory;
        }

        // Get all rows from cache if none is hold in memory.
        if ($actualItemsInMemory === 0) {
            return $this->cache->getMultiple($keys, $default);
        }

        // Add missing values from cache.
        foreach ($this->cache->getMultiple($keys, $default) as $key => $value) {
            if (null !== $value) {
                $memory[$key] = $value;
            }
        }

        return $memory;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        if (func_num_args() === 1) {
            $ttl = value($this->defaultTTL);
        }

        $this->memory->setMultiple($values, $ttl);

        if ($this->memory->reachedMemoryLimit()) {
            return $this->cache->setMultiple($this->memory->flush(), $ttl);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = is_array($keys) ? $keys : iterator_to_array($keys);

        $this->memory->deleteMultiple($keys);

        return $this->cache->deleteMultiple($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        if ($this->memory->has($key)) {
            return true;
        }

        return $this->cache->has($key);
    }
}
