<?php

namespace Orchestra\Model;

use Closure;
use Illuminate\Database\Eloquent\Model;

abstract class Eloquent extends Model
{
    /**
     * Determine if the model instance uses soft deletes.
     *
     * @return bool
     */
    public function isSoftDeleting()
    {
        return (property_exists($this, 'forceDeleting') && $this->forceDeleting === false);
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param  \Closure  $callback
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    public function transaction(Closure $callback)
    {
        return $this->getConnection()->transaction($callback);
    }
}
