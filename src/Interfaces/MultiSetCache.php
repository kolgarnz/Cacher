<?php

namespace Kolgarnz\Cacher\Interfaces;

interface MultiSetCache
{
    /**
     * @param array[] $data Array of keys => data to save
     * @param int $lifetime The lifetime. If != 0, sets a specific lifetime for this
     *                           cache entry (0 => infinite lifeTime).
     * @return bool
     */
    function saveMultiple(array $data, $lifetime);
}