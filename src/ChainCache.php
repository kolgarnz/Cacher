<?php

namespace Kolgarnz\Cacher;

class ChainCache extends CacheProvider
{
    /**
     * @var CacheProvider[]
     */
    private $cacheProviders = array();

    /**
     * Constructor
     *
     * @param CacheProvider[] $cacheProviders
     */
    public function __construct($cacheProviders = array())
    {
        $this->cacheProviders = $cacheProviders;
    }

    /**
     * {@inheritDoc}
     */
    public function setNamespace($namespace)
    {
        parent::setNamespace($namespace);
        foreach ($this->cacheProviders as $cacheProvider) {
            $cacheProvider->setNamespace($namespace);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetch($id)
    {
        foreach ($this->cacheProviders as $key => $cacheProvider) {
            if ($cacheProvider->doContains($id)) {
                $value = $cacheProvider->doFetch($id);
                // We populate all the previous cache layers (that are assumed to be faster)
                for ($subKey = $key - 1; $subKey >= 0; $subKey--) {
                    $this->cacheProviders[$subKey]->doSave($id, $value);
                }
                return $value;
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFetchMultiple(array $keys)
    {
        $result = array();
        foreach ($this->cacheProviders as $key => $cacheProvider) {
            $providerResult = $cacheProvider->doFetchMultiple($keys);
            $keys = $this->getNeedCache($providerResult);
            $toSaveResult = array_filter($providerResult, function () use ($key) {
                return !($key === null);
            });
            if (!empty($toSaveResult)) {
                $result = array_merge($result, $toSaveResult);
                for ($subKey = $key - 1; $subKey >= 0; $subKey--) {
                    $this->cacheProviders[$subKey]->doSaveMultiple($toSaveResult);
                }
            }
        }
        if (!empty($keys)) {
            foreach ($keys as $key) {
                $result[$key] = null;
            }
        }
        return $result;
    }

    /**
     * Function to get keys array of empty elements in array
     *
     * @see doFetchMultiple
     * @param array $data Array returned from CacheProvider->doFetchMultiple
     * @return array Array of keys that should be get
     */
    protected function getNeedCache(array $data)
    {
        $result = array();
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                $result[] = $key;
            }
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function doContains($id)
    {
        foreach ($this->cacheProviders as $cacheProvider) {
            if ($cacheProvider->doContains($id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function doSave($id, $data, $lifeTime = 0)
    {
        $stored = true;
        foreach ($this->cacheProviders as $cacheProvider) {
            $stored = $cacheProvider->doSave($id, $data, $lifeTime) && $stored;
        }
        return $stored;
    }

    /**
     * {@inheritDoc}
     */
    protected function doSaveMultiple(array $data, $lifeTime = 0)
    {
        $stored = true;
        foreach ($this->cacheProviders as $cacheProvider) {
            $stored = $cacheProvider->doSaveMultiple($data, $lifeTime) && $stored;
        }
        return $stored;
    }

    /**
     * {@inheritDoc}
     */
    protected function doDelete($id)
    {
        $deleted = true;
        foreach ($this->cacheProviders as $cacheProvider) {
            $deleted = $cacheProvider->doDelete($id) && $deleted;
        }
        return $deleted;
    }

    /**
     * {@inheritDoc}
     */
    protected function doDeleteMultiple(array $keys)
    {
        $deleted = true;
        foreach ($this->cacheProviders as $cacheProvider) {
            $deleted = $cacheProvider->doDeleteMultiple($keys) && $deleted;
        }
        return $deleted;
    }

    /**
     * {@inheritDoc}
     */
    protected function doFlush()
    {
        $flushed = true;
        foreach ($this->cacheProviders as $cacheProvider) {
            $flushed = $cacheProvider->doFlush() && $flushed;
        }
        return $flushed;
    }
}