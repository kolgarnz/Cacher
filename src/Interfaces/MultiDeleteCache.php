<?php

namespace Kolgarnz\Cacher\Interfaces;

interface MultiDeleteCache
{
    /**
     * Deletes a cache entries.
     *
     * @param array $keys array of cache Ids
     *
     * @return bool TRUE if the cache entries was successfully deleted, FALSE otherwise.
     */
    function deleteMultiple(array $keys);
}