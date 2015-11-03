<?php

namespace Kolgarnz\Cacher\Interfaces;

interface MultiGetCache
{
    /**
     * Returns an associative array of values for keys.
     *
     * @param string[] $keys Array of keys to retrieve from cache
     * @return mixed[] Array of retrieved values, indexed by the specified keys.
     */
    function fetchMultiple(array $keys);

}