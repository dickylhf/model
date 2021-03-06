<?php

namespace Orchestra\Model\TestCase;

use Mockery as m;
use Orchestra\Model\Role;
use PHPUnit\Framework\TestCase;
use Orchestra\Support\Traits\Testing\MockEloquentConnection;

class RoleTest extends TestCase
{
    use MockEloquentConnection;

    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        Role::setDefaultRoles(['admin' => 10, 'member' => 20]);
    }

    /**
     * Teardown the test environment.
     */
    protected function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Model\Role::users() method.
     *
     * @test
     */
    public function testUsersMethod()
    {
        $model = new Role();

        $this->addMockConnection($model);

        $stub = $model->users();

        $this->assertInstanceOf('\Illuminate\Database\Eloquent\Relations\BelongsToMany', $stub);
        $this->assertInstanceOf('\Orchestra\Model\User', $stub->getQuery()->getModel());
    }

    /**
     * Test Orchestra\Model\Role::admin() method.
     *
     * @test
     */
    public function testAdminMethod()
    {
        $model = new Role();

        $resolver = m::mock('Illuminate\Database\ConnectionResolverInterface');
        $model->setConnectionResolver($resolver);
        $resolver->shouldReceive('connection')
            ->andReturn($connection = m::mock('Illuminate\Database\Connection'));
        $model->getConnection()
            ->shouldReceive('getQueryGrammar')
                ->andReturn($grammar = m::mock('Illuminate\Database\Query\Grammars\Grammar'));
        $model->getConnection()
            ->shouldReceive('getPostProcessor')
                ->andReturn($processor = m::mock('Illuminate\Database\Query\Processors\Processor'));

        $grammar->shouldReceive('compileSelect')->once()->andReturn('SELECT * FROM `roles` WHERE id=?');
        $connection->shouldReceive('getName')->once()->andReturn('mysql')
            ->shouldReceive('select')->once()
                ->with('SELECT * FROM `roles` WHERE id=?', [10], true)->andReturn(null);
        $processor->shouldReceive('processSelect')->once()->andReturn([]);

        $model->admin();
    }

    /**
     * Test Orchestra\Model\Role::member() method.
     *
     * @test
     */
    public function testMemberMethod()
    {
        $model = new Role();

        $resolver = m::mock('Illuminate\Database\ConnectionResolverInterface');
        $model->setConnectionResolver($resolver);
        $resolver->shouldReceive('connection')
            ->andReturn($connection = m::mock('Illuminate\Database\Connection'));
        $model->getConnection()
            ->shouldReceive('getQueryGrammar')
                ->andReturn($grammar = m::mock('Illuminate\Database\Query\Grammars\Grammar'));
        $model->getConnection()
            ->shouldReceive('getPostProcessor')
                ->andReturn($processor = m::mock('Illuminate\Database\Query\Processors\Processor'));

        $grammar->shouldReceive('compileSelect')->once()->andReturn('SELECT * FROM `roles` WHERE id=?');
        $connection->shouldReceive('getName')->once()->andReturn('mysql')
            ->shouldReceive('select')->once()
                ->with('SELECT * FROM `roles` WHERE id=?', [20], true)->andReturn(null);
        $processor->shouldReceive('processSelect')->once()->andReturn([]);

        $model->member();
    }
}
