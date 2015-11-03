<?php

namespace Kolgarnz\Cacher\Interfaces;

interface FlushableCache
{
    /**
     * Flushes all cache entries, globally.
     *
     * @return bool TRUE if the cache entries were successfully flushed, FALSE otherwise.
     */
    public function flushAll();
}