<?php

namespace Kolgarnz\Cacher;

use Kolgarnz\Cacher\Interfaces\MultiDeleteCache;
use Kolgarnz\Cacher\Interfaces\MultiGetCache;
use Kolgarnz\Cacher\Interfaces\MultiSetCache;

abstract class CacheProvider extends \Doctrine\Common\Cache\CacheProvider implements MultiSetCache, MultiDeleteCache, MultiGetCache
{
    const NAMESPACE_CACHEKEY = 'NamespaceCacheKey[%s]';

    /**
     * The namespace to prefix all cache ids with.
     *
     * @var string
     */
    private $namespace = '';

    /**
     * The namespace version.
     *
     * @var integer|null
     */
    private $namespaceVersion;



    /**
     * {@inheritdoc}
     */
    public function fetchMultiple(array $keys)
    {
        if (empty($keys)) {
            return array();
        }

        $namespacedKeys = $this->getMultiNamespacedId($keys);

        $cachedData = $this->doFetchMultiple($namespacedKeys);
        $items = array();
        // no internal array function supports this sort of mapping: needs to be iterative
        // this filters and combines keys in one pass
        foreach ($namespacedKeys as $requestedKey => $namespacedKey) {
            if (isset($cachedData[$namespacedKey]) || array_key_exists($namespacedKey, $cachedData)) {
                $items[$requestedKey] = $cachedData[$namespacedKey];
            }
        }
        return $items;
    }



    /**
     * {@inheritdoc}
     */
    public function saveMultiple(array $data, $lifeTime = 0)
    {
        if (empty($data)) {
            return false;
        }
        return $this->doSaveMultiple($this->getNamespacedData($data), $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(array $keys)
    {
        return $this->doDeleteMultiple($this->getMultiNamespacedId($keys));
    }


    /**
     * Default implementation of doFetchMultiple. Each driver that supports multi-get should overwrite it.
     *
     * @param array $keys Array of keys to retrieve from cache
     * @return array Array of values retrieved for the given keys.
     */
    protected function doFetchMultiple(array $keys)
    {
        $returnValues = array();
        foreach ($keys as $index => $key) {
            if (false !== ($item = $this->doFetch($key))) {
                $returnValues[$key] = $item;
            }
        }
        return $returnValues;
    }


    /**
     * Puts data into the cache.
     *
     * @param array $data Array of key => value
     * @param int $lifeTime The lifetime. If != 0, sets a specific lifetime for this
     *                           cache entry (0 => infinite lifeTime).
     * @return bool TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    abstract protected function doSaveMultiple(array $data, $lifeTime = 0);


    /**
     * Deletes a cache entries.
     *
     * @param array $keys array of cache Ids
     *
     * @return bool TRUE if the cache entries was successfully deleted, FALSE otherwise.
     */
    abstract protected function doDeleteMultiple(array $keys);


    private function getMultiNamespacedId(array $keys)
    {
        $namespaceVersion = $this->getNamespaceVersion();

        $result = array();
        foreach ($keys as $key) {
            $result[$key] = sprintf('%s[%s][%s]', $this->namespace, $key, $namespaceVersion);
        }
        return $result;
    }

    private function getNamespacedData(array $data)
    {
        $namespacedKeys = $this->getMultiNamespacedId(array_keys($data));

        $result = array();
        foreach ($data as $notNamespacedKey => $namespacedData) {
            $result[$namespacedKeys[$notNamespacedKey]] = $namespacedData;
        }

        return $result;
    }

    /**
     * Prefixes the passed id with the configured namespace value.
     *
     * @param string $id The id to namespace.
     *
     * @return string The namespaced id.
     */
    private function getNamespacedId($id)
    {
        $namespaceVersion = $this->getNamespaceVersion();

        return sprintf('%s[%s][%s]', $this->namespace, $id, $namespaceVersion);
    }

    /**
     * Returns the namespace cache key.
     *
     * @return string
     */
    private function getNamespaceCacheKey()
    {
        return sprintf(self::NAMESPACE_CACHEKEY, $this->namespace);
    }

    /**
     * Returns the namespace version.
     *
     * @return integer
     */
    private function getNamespaceVersion()
    {
        if (null !== $this->namespaceVersion) {
            return $this->namespaceVersion;
        }

        $namespaceCacheKey = $this->getNamespaceCacheKey();
        $this->namespaceVersion = $this->doFetch($namespaceCacheKey) ?: 1;

        return $this->namespaceVersion;
    }

}