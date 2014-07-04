<?php
/**
 * @package     FrameworkOnFramework.UnitTest
 * @subpackage  F0FEncryptTotp
 *
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for F0FEncryptTotp
 *
 * @package  FrameworkOnFramework.UnitTest
 * @since    x.y
 */
class FOFEncryptTotpTest extends PHPUnit_Framework_TestCase
{
	/**
	 * [setUp description]
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		// VARS: $timeStep = 30, $passCodeLength = 6, $secretLength = 10, $base32=null
		$timeStep 				= 30;
		$passCodeLength 		= 6;
		$secretLength 			= 10;
		$this->secretLength 	= $secretLength;

		$this->theThing = new \FOF30\Encrypt\Totp($timeStep, $passCodeLength, $secretLength);
	}

	/**
	 * [tearDown description]
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		// Grab a beer
	}

	/*
	 *	TESTS
	 */

	/**
	 * [testGetPeriod description]
	 *
	 * @return  void
	 */
	public function testGetPeriod()
	{
		// Time as I wrote the test 1375000339 -> 45833344
		$this->assertEquals(45833344, $this->theThing->getPeriod(1375000339));
	}

	/**
	 * [testGetcode description]
	 *
	 * @return  void
	 */
	public function testGetcode()
	{
		// KREECVCJKNKE6VCBJRGFSU2FINJEKVA, 1375000339 -> 567377
		$this->assertEquals(567377, $this->theThing->getCode('KREECVCJKNKE6VCBJRGFSU2FINJEKVA', 1375000339));
	}

	/**
	 * [testGetUrl description]
	 *
	 * @return  void
	 */
	public function testGetUrl()
	{
		// KREECVCJKNKE6VCBJRGFSU2FINJEKVA, 1375000339 -> 567377
		$this->assertEquals(
			'https://chart.googleapis.com/chart?chs=200x200&chld=Q|2&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FJohnnieWalker%40joomla.org%3Fsecret%3DKREECVCJKNKE6VCBJRGFSU2FINJEKVA',
			$this->theThing->getUrl('JohnnieWalker', 'joomla.org', 'KREECVCJKNKE6VCBJRGFSU2FINJEKVA')
			);
	}
}
