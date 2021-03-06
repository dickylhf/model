<?php

namespace Orchestra\Model\Traits;

use Illuminate\Database\Eloquent\Model;

trait OwnedBy
{
    /**
     * Check if related model actually owns the relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $related
     * @param  string|null  $key
     *
     * @return bool
     */
    public function ownedBy(Model $related = null, $key = null)
    {
        if (is_null($related)) {
            return false;
        }

        if (is_null($key)) {
            $key = $related->getForeignKey();
        }

        return $this->getAttribute($key) == $related->getKey();
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    abstract public function getAttribute($key);
}
