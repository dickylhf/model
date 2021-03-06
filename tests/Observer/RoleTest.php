<?php

namespace Orchestra\Model\TestCase\Observer;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Orchestra\Model\Observer\Role as RoleObserver;

class RoleTest extends TestCase
{
    /**
     * Teardown the test environment.
     */
    protected function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Model\Observer\Role::creating()
     * method.
     *
     * @test
     */
    public function testCreatingMethod()
    {
        $acl = m::mock('Orchestra\Contracts\Authorization\Factory');
        $model = m::mock('\Orchestra\Model\Role');

        $model->shouldReceive('getAttribute')->once()->with('name')->andReturn('foo');
        $acl->shouldReceive('addRole')->once()->with('foo')->andReturn(null);

        $stub = new RoleObserver($acl);
        $stub->creating($model);
    }

    /**
     * Test Orchestra\Model\Observer\Role::deleting()
     * method.
     *
     * @test
     */
    public function testDeletingMethod()
    {
        $acl = m::mock('Orchestra\Contracts\Authorization\Factory');
        $model = m::mock('\Orchestra\Model\Role');

        $model->shouldReceive('getAttribute')->once()->with('name')->andReturn('foo');
        $acl->shouldReceive('removeRole')->once()->with('foo')->andReturn(null);

        $stub = new RoleObserver($acl);
        $stub->deleting($model);
    }

    /**
     * Test Orchestra\Model\Observer\Role::updating()
     * method.
     *
     * @test
     */
    public function testUpdatingMethod()
    {
        $acl = m::mock('Orchestra\Contracts\Authorization\Factory');
        $model = m::mock('\Orchestra\Model\Role');

        $model->shouldReceive('getOriginal')->once()->with('name')->andReturn('foo')
            ->shouldReceive('getAttribute')->once()->with('name')->andReturn('foobar')
            ->shouldReceive('getDeletedAtColumn')->never()->andReturn('deleted_at')
            ->shouldReceive('isSoftDeleting')->once()->andReturn(false);
        $acl->shouldReceive('renameRole')->once()->with('foo', 'foobar')->andReturn(null);

        $stub = new RoleObserver($acl);
        $stub->updating($model);
    }

    /**
     * Test Orchestra\Model\Observer\Role::updating()
     * method for restoring.
     *
     * @test
     */
    public function testUpdatingMethodForRestoring()
    {
        $acl = m::mock('Orchestra\Contracts\Authorization\Factory');
        $model = m::mock('\Orchestra\Model\Role');

        $model->shouldReceive('getOriginal')->once()->with('name')->andReturn('foo')
            ->shouldReceive('getAttribute')->once()->with('name')->andReturn('foobar')
            ->shouldReceive('getDeletedAtColumn')->once()->andReturn('deleted_at')
            ->shouldReceive('isSoftDeleting')->once()->andReturn(true)
            ->shouldReceive('getOriginal')->once()->with('deleted_at')->andReturn('0000-00-00 00:00:00')
            ->shouldReceive('getAttribute')->once()->with('deleted_at')->andReturn(null);
        $acl->shouldReceive('addRole')->once()->with('foobar')->andReturn(null);

        $stub = new RoleObserver($acl);
        $stub->updating($model);
    }
}
