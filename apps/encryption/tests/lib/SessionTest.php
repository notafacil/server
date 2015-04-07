<?php
/**
 * @author Clark Tomlinson  <clark@owncloud.com>
 * @since 3/31/15, 10:19 AM
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */


namespace OCA\Encryption\Tests;


use OCA\Encryption\Session;
use Test\TestCase;

class SessionTest extends TestCase {
	private static $tempStorage = [];
	/**
	 * @var Session
	 */
	private $instance;
	private $sessionMock;

	/**
	 * @expectedException \OCA\Encryption\Exceptions\PrivateKeyMissingException
	 * @expectedExceptionMessage Private Key missing for user: please try to log-out and log-in again
	 */
	public function testThatGetPrivateKeyThrowsExceptionWhenNotSet() {
		$this->instance->getPrivateKey();
	}

	/**
	 * @depends testThatGetPrivateKeyThrowsExceptionWhenNotSet
	 */
	public function testSetAndGetPrivateKey() {
		$this->instance->setPrivateKey('dummyPrivateKey');
		$this->assertEquals('dummyPrivateKey', $this->instance->getPrivateKey());

	}

	/**
	 * @depends testSetAndGetPrivateKey
	 */
	public function testIsPrivateKeySet() {
		$this->assertTrue($this->instance->isPrivateKeySet());

		unset(self::$tempStorage['privateKey']);
		$this->assertFalse($this->instance->isPrivateKeySet());

		// Set private key back so we can test clear method
		self::$tempStorage['privateKey'] = 'dummyPrivateKey';
	}

	/**
	 *
	 */
	public function testSetAndGetStatusWillSetAndReturn() {
		// Check if get status will return 0 if it has not been set before
		$this->assertEquals(0, $this->instance->getStatus());

		$this->instance->setStatus(Session::NOT_INITIALIZED);
		$this->assertEquals(0, $this->instance->getStatus());

		$this->instance->setStatus(Session::INIT_EXECUTED);
		$this->assertEquals(1, $this->instance->getStatus());

		$this->instance->setStatus(Session::INIT_SUCCESSFUL);
		$this->assertEquals(2, $this->instance->getStatus());
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function setValueTester($key, $value) {
		self::$tempStorage[$key] = $value;
	}

	/**
	 * @param $key
	 */
	public function removeValueTester($key) {
		unset(self::$tempStorage[$key]);
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function getValueTester($key) {
		if (!empty(self::$tempStorage[$key])) {
			return self::$tempStorage[$key];
		}
		return null;
	}

	/**
	 *
	 */
	public function testClearWillRemoveValues() {
		$this->instance->clear();
		$this->assertEmpty(self::$tempStorage);
	}

	/**
	 *
	 */
	protected function setUp() {
		parent::setUp();
		$this->sessionMock = $this->getMock('OCP\ISession');

		$this->sessionMock->expects($this->any())
			->method('set')
			->will($this->returnCallback([$this, "setValueTester"]));

		$this->sessionMock->expects($this->any())
			->method('get')
			->will($this->returnCallback([$this, "getValueTester"]));

		$this->sessionMock->expects($this->any())
			->method('remove')
			->will($this->returnCallback([$this, "removeValueTester"]));


		$this->instance = new Session($this->sessionMock);
	}
}
