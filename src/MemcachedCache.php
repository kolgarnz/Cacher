<?php

namespace Kolgarnz\Cacher;

use Doctrine\Common\Cache\Cache;
use Memcached;

class MemcachedCache extends CacheProvider
{
    /**
     * @var Memcached|null
     */
    private $memcached;

    /**
     * Sets the memcache instance to use.
     *
     * @param Memcached $memcached
     *
     * @return void
     */
    public function setMemcached(Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    /**
     * Gets the memcached instance used by the cache.
     *
     * @return Memcached|null
     */
    public function getMemcached()
    {
        return $this->memcached;
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetch($id)
    {
        return $this->memcached->get($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFetchMultiple(array $keys)
    {
        $null = null;
        $result = $this->memcached->getMulti($keys, $null, \Memcached::GET_PRESERVE_ORDER);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function doContains($id)
    {
        return $this->memcached->get($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        if ($lifeTime > 30 * 24 * 3600) {
            $lifeTime = time() + $lifeTime;
        }
        return $this->memcached->set($id, $data, (int)$lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSaveMultiple(array $data, $lifeTime = 0)
    {
        if ($lifeTime > 30 * 24 * 3600) {
            $lifeTime = time() + $lifeTime;
        }
        return $this->memcached->setMulti($data, (int)$lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($id)
    {
        return $this->memcached->delete($id)
        || $this->memcached->getResultCode() === Memcached::RES_NOTFOUND;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDeleteMultiple(array $keys)
    {
        return $this->memcached->deleteMulti($keys);
    }

    /**
     * {@inheritdoc}
     */
    protected function doFlush()
    {
        return $this->memcached->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetStats()
    {
        $stats = $this->memcached->getStats();
        $servers = $this->memcached->getServerList();
        $key = $servers[0]['host'] . ':' . $servers[0]['port'];
        $stats = $stats[$key];
        return array(
            Cache::STATS_HITS => $stats['get_hits'],
            Cache::STATS_MISSES => $stats['get_misses'],
            Cache::STATS_UPTIME => $stats['uptime'],
            Cache::STATS_MEMORY_USAGE => $stats['bytes'],
            Cache::STATS_MEMORY_AVAILABLE => $stats['limit_maxbytes'],
        );
    }
}