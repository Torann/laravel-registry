<?php namespace Torann\Registry\Timestamps;

interface TimestampInterface {

    /**
     * Check for expired cache timestamp
     *
     * @param  string $cached_at
     * @return bool
     */
    public function check($cached_at);

    /**
     * Update timestamp.
     *
     * @param  string $cached_at
     * @return bool
     */
    public function update($cached_at);
}