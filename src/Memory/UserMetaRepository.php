<?php

namespace Orchestra\Model\Memory;

use Illuminate\Support\Arr;
use Orchestra\Memory\Handler;
use Orchestra\Model\UserMeta;
use Illuminate\Contracts\Container\Container;
use Orchestra\Contracts\Memory\Handler as HandlerContract;

class UserMetaRepository extends Handler implements HandlerContract
{
    /**
     * Storage name.
     *
     * @var string
     */
    protected $storage = 'user';

    /**
     * Cached user meta.
     *
     * @var array
     */
    protected $userMeta = [];

    /**
     * Setup a new memory handler.
     *
     * @param  string  $name
     * @param  array  $config
     * @param  \Illuminate\Contracts\Container\Container  $repository
     */
    public function __construct($name, array $config, Container $repository)
    {
        $this->repository = $repository;

        parent::__construct($name, $config);
    }

    /**
     * Initiate the instance.
     *
     * @return array
     */
    public function initiate()
    {
        return [];
    }

    /**
     * Get value from database.
     *
     * @param  string   $key
     *
     * @return mixed
     */
    public function retrieve($key)
    {
        list($name, $userId) = explode('/user-', $key);

        if (! isset($this->userMeta[$userId])) {
            $data = $this->getModel()->where('user_id', '=', $userId)->get();

            $this->userMeta[$userId] = $this->processRetrievedData($userId, $data);
        }

        return Arr::get($this->userMeta, "{$userId}.{$name}");
    }

    /**
     * Add a finish event.
     *
     * @param  array  $items
     *
     * @return bool
     */
    public function finish(array $items = [])
    {
        foreach ($items as $key => $value) {
            $this->save($key, $value);
        }

        return true;
    }

    /**
     * Process retrieved data.
     *
     * @param  string|int  $userId
     * @param  \Illuminate\Support\Collection|array  $data
     *
     * @return void
     */
    protected function processRetrievedData($userId, $data = [])
    {
        $items = [];

        foreach ($data as $meta) {
            if (! $value = @unserialize($meta->getAttribute('value'))) {
                $value = $meta->getAttribute('value');
            }

            $key = $meta->getAttribute('name');

            $this->addKey("{$key}/user-{$userId}", [
                'id'    => $meta->getAttribute('id'),
                'value' => $value,
            ]);

            $items[$key] = $value;
        }

        return $items;
    }

    /**
     * Save user meta to memory.
     *
     * @param  mixed    $key
     * @param  mixed    $value
     *
     * @return void
     */
    protected function save($key, $value)
    {
        $isNew = $this->isNewKey($key);

        list($name, $userId) = explode('/user-', $key);

        // We should be able to ignore this if user id is empty or checksum
        // return the same value (no change occured).
        if ($this->check($key, $value) || empty($userId)) {
            return ;
        }

        $this->saving($name, $userId, $value, $isNew);
    }

    /**
     * Process saving the value to memory.
     *
     * @param  string  $name
     * @param  mixed   $userId
     * @param  mixed   $value
     * @param  bool    $isNew
     *
     * @return void
     */
    protected function saving($name, $userId, $value = null, $isNew = true)
    {
        $meta = $this->getModel()->search($name, $userId)->first();

        // Deleting a configuration is determined by ':to-be-deleted:'. It
        // would be extremely weird if that is used for other purposed.
        if (is_null($value) || $value === ':to-be-deleted:') {
            ! is_null($meta) && $meta->delete();
            return ;
        }

        // If the content is a new configuration, let push it as a insert
        // instead of an update to Eloquent.
        if (true === $isNew && is_null($meta)) {
            $meta = $this->getModel();

            $meta->setAttribute('name', $name);
            $meta->setAttribute('user_id', $userId);
        }

        $meta->setAttribute('value', serialize($value));
        $meta->save();
    }

    /**
     * Get model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getModel()
    {
        return $this->repository->make(UserMeta::class)->newInstance();
    }
}
