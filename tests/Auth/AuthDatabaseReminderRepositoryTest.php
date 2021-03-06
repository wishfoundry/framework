<?php

use Mockery as m;

class AuthDatabaseReminderRepositoryTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testCreateInsertsNewRecordIntoTable()
	{
		$repo = $this->getRepo();
		$repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
		$query->shouldReceive('insert')->andReturnUsing(function($payload) { return $payload; });
		$user = m::mock('Illuminate\Auth\RemindableInterface');
		$user->shouldReceive('getReminderEmail')->andReturn('email');

		$results = $repo->create($user);

		$this->assertEquals('email', $results['email']);
		$this->assertTrue(is_string($results['token']));
		$this->assertInstanceOf('DateTime', $results['created_at']);
	}


	public function testExistReturnsFalseIfNoRowFoundForUser()
	{
		$repo = $this->getRepo();
		$repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
		$query->shouldReceive('where')->once()->with('email', 'email')->andReturn($query);
		$query->shouldReceive('where')->once()->with('token', 'token')->andReturn($query);
		$query->shouldReceive('first')->andReturn(null);
		$user = m::mock('Illuminate\Auth\RemindableInterface');
		$user->shouldReceive('getReminderEmail')->andReturn('email');

		$this->assertFalse($repo->exists($user, 'token'));
	}


	public function testExistReturnsFalseIfRecordIsExpired()
	{
		$repo = $this->getRepo();
		$repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
		$query->shouldReceive('where')->once()->with('email', 'email')->andReturn($query);
		$query->shouldReceive('where')->once()->with('token', 'token')->andReturn($query);
		$date = date('Y-m-d H:i:s', time() - 300000);
		$query->shouldReceive('first')->andReturn((object) array('created_at' => $date));
		$user = m::mock('Illuminate\Auth\RemindableInterface');
		$user->shouldReceive('getReminderEmail')->andReturn('email');

		$this->assertFalse($repo->exists($user, 'token'));
	}


	public function testExistReturnsTrueIfValidRecordExists()
	{
		$repo = $this->getRepo();
		$repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
		$query->shouldReceive('where')->once()->with('email', 'email')->andReturn($query);
		$query->shouldReceive('where')->once()->with('token', 'token')->andReturn($query);
		$date = date('Y-m-d H:i:s', time() - 200000);
		$query->shouldReceive('first')->andReturn((object) array('created_at' => $date));
		$user = m::mock('Illuminate\Auth\RemindableInterface');
		$user->shouldReceive('getReminderEmail')->andReturn('email');

		$this->assertTrue($repo->exists($user, 'token'));
	}


	public function testDeleteMethodDeletesByToken()
	{
		$repo = $this->getRepo();
		$repo->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($query = m::mock('StdClass'));
		$query->shouldReceive('where')->once()->with('token', 'token')->andReturn($query);
		$query->shouldReceive('delete')->once();

		$repo->delete('token');
	}


	protected function getRepo()
	{
		return new Illuminate\Auth\DatabaseReminderRepository(m::mock('Illuminate\Database\Connection'), 'table', 'key');
	}

}