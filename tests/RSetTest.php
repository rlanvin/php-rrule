<?php

use RRule\RSet;
use RRule\RRule;

class RSetTest extends PHPUnit_Framework_TestCase
{
	public function testIsInfinite()
	{
		$rset = new RSet();
		$this->assertFalse($rset->isInfinite());
		$this->assertTrue($rset->isFinite());

		$rset->addRRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 10
		));
		$this->assertFalse($rset->isInfinite());
		$this->assertTrue($rset->isFinite());

		$rset->addRRule(array(
			'FREQ' => 'YEARLY'
		));
		$this->assertTrue($rset->isInfinite());
		$this->assertFalse($rset->isFinite());
	}

	public function testAddRRule()
	{
		$rset = new RSet();
		$rset->addRRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 2,
			'BYDAY' => 'TU',
			'DTSTART' => date_create('1997-09-02 09:00')
		));
		$rset->addRRule(new RRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 1,
			'BYDAY' => 'TH',
			'DTSTART' => date_create('1997-09-02 09:00')
		)));

		$this->assertEquals(array(
			date_create('1997-09-02 09:00'),
			date_create('1997-09-04 09:00'),
			date_create('1997-09-09 09:00')
		), $rset->getOccurrences());
	}

	public function testAddDate()
	{
		$rset = new RSet();
		$rset->addRRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 1,
			'BYDAY' => 'TU',
			'DTSTART' => date_create('1997-09-02 09:00')
		));
		$rset->addDate(date_create('1997-09-04 09:00'));
		$rset->addDate(date_create('1997-09-09 09:00'));
		$this->assertEquals(array(
			date_create('1997-09-02 09:00'),
			date_create('1997-09-04 09:00'),
			date_create('1997-09-09 09:00')
		), $rset->getOccurrences());
	}

	public function testAddExRule()
	{
		$rset = new RSet();
		$rset->addRRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 6,
			'BYDAY' => 'TU,TH',
			'DTSTART' => date_create('1997-09-02 09:00')
		));
		$rset->addExRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 3,
			'BYDAY' => 'TH',
			'DTSTART' => date_create('1997-09-02 09:00')
		));
		$this->assertEquals(array(
			date_create('1997-09-02 09:00'),
			date_create('1997-09-09 09:00'),
			date_create('1997-09-16 09:00')
		), $rset->getOccurrences());
	}

	public function testAddExDate()
	{
		$rset = new RSet();
		$rset->addRRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 6,
			'BYDAY' => 'TU, TH',
			'DTSTART' => date_create('1997-09-02 09:00')
		));
		$rset->addExdate('1997-09-04 09:00:00');
		$rset->addExdate('1997-09-11 09:00:00');
		$rset->addExdate('1997-09-18 09:00:00');

		$this->assertEquals(array(
			date_create('1997-09-02 09:00'),
			date_create('1997-09-09 09:00'),
			date_create('1997-09-16 09:00')
		), $rset->getOccurrences());
	}

	public function testAddDateAndExRule()
	{
		// TODO
	}

	public function testCountable()
	{
		// TODO
	}

	public function testRSetInRset()
	{
		$rset = new RSet();
		$rset->addRRule($rset);
		$rset->addDate('2016-03-21');

		$this->assertEquals(
			array(date_create('2016-03-21')),
			$rset->getOccurrences(),
			'Adding the RSet into itself does not explode'
		);

		$sub_rset = new RSet();
		$sub_rset->addDate('2016-03-21 10:00');
		$sub_rset->addDate('2016-03-21 11:00');

		$rset = new RSet();
		$rset->addRRule($sub_rset);

		$this->assertEquals(array(
			date_create('2016-03-21 10:00'),
			date_create('2016-03-21 11:00')
		), $rset->getOccurrences());

		$rset->addExDate('2016-03-21 11:00');
		$this->assertEquals(array(
			date_create('2016-03-21 10:00')
		), $rset->getOccurrences());
	}
}