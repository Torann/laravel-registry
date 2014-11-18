<?php namespace Torann\Registry\Timestamps;

use Illuminate\Support\Facades\Redis as Timestamp;

class Redis implements TimestampInterface {

    /**
     * Check for expired cache timestamp
     *
     * @param  string $cached_at
     * @return bool
     */
    public function check($cached_at)
    {
        $timestamp = Timestamp::get('registry_updated_at');

        return ($timestamp && $timestamp > $cached_at);
    }

    /**
     * Update timestamp.
     *
     * @param  string $cached_at
     * @return bool
     */
    public function update($cached_at)
    {
        return Timestamp::set('registry_updated_at', $cached_at);
    }
}
