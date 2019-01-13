<?php

namespace RRule\Tests;

use RRule\RRule;
use RRule\Proxy;
use PHPUnit\Framework\TestCase;

class ProxyTest extends TestCase
{
	public function testIterator()
	{
		$proxy = new Proxy(
			new RRule([
				'FREQ' => 'MONTHLY',
				'COUNT' => 3,
				'BYMONTHDAY' => 31,
				'DTSTART' => '1997-09-02'
			]),
			function (\DateTimeInterface $occurrence) {
				return new \RRule\Event($occurrence, 3600);
			}
		);

		$expected = [
			new \RRule\Event(date_create('1997-10-31'), 3600),
			new \RRule\Event(date_create('1997-12-31'), 3600),
			new \RRule\Event(date_create('1998-01-31'), 3600),
		];

		$n = 0;
		foreach ( $proxy as $event ) {
			$this->assertEquals($expected[$n],$event);
			$n++;
		}
	}

	public function testGetOccurrences()
	{
		$proxy = new Proxy(
			new RRule([
				'FREQ' => 'MONTHLY',
				'COUNT' => 3,
				'BYMONTHDAY' => 31,
				'DTSTART' => '1997-09-02'
			]),
			function (\DateTimeInterface $occurrence) {
				return new \RRule\Event($occurrence, 3600);
			}
		);

		$expected = [
			new \RRule\Event(date_create('1997-10-31'), 3600),
			new \RRule\Event(date_create('1997-12-31'), 3600),
			new \RRule\Event(date_create('1998-01-31'), 3600),
		];

		$this->assertEquals($expected, iterator_to_array($proxy->getOccurrences()));
	}

	public function testGetOccurrencesBetween()
	{
		$proxy = new Proxy(
			new RRule([
				'FREQ' => 'MONTHLY',
				'COUNT' => 3,
				'BYMONTHDAY' => 31,
				'DTSTART' => '1997-09-02'
			]),
			function (\DateTimeInterface $occurrence) {
				return new \RRule\Event($occurrence, 3600);
			}
		);

		$expected = [
			new \RRule\Event(date_create('1997-10-31'), 3600),
			new \RRule\Event(date_create('1997-12-31'), 3600)
		];

		$this->assertEquals($expected, iterator_to_array($proxy->getOccurrencesBetween('1997-10-31', '1997-12-31')));
	}
}