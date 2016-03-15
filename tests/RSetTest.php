<?php

use RRule\RSet;

class RSetTest extends PHPUnit_Framework_TestCase
{
	public function testAddRRule()
	{
		$rrset = new RSet();
		$rrset->addRRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 2,
			'BYDAY' => 'TU',
			'DTSTART' => date_create('1997-09-02 09:00')
		));
		$rrset->addRRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 1,
			'BYDAY' => 'TH',
			'DTSTART' => date_create('1997-09-02 09:00')
		));

		$this->assertEquals(array(
			date_create('1997-09-02 09:00'),
			date_create('1997-09-04 09:00'),
			date_create('1997-09-09 09:00')
		), $rrset->getOccurrences());
	}

	public function testAddRDate()
	{
		
	}

	public function testAddExRule()
	{
		
	}

	public function testAddExDate()
	{
		$rrset = new RSet();
		$rrset->addRRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 6,
			'BYDAY' => 'TU, TH',
			'DTSTART' => date_create('1997-09-02 09:00')
		));
		$rrset->addExdate('1997-09-04 09:00:00');
		$rrset->addExdate('1997-09-11 09:00:00');
		$rrset->addExdate('1997-09-18 09:00:00');

		$this->assertEquals(array(
			date_create('1997-09-02 09:00'),
			date_create('1997-09-09 09:00'),
			date_create('1997-09-16 09:00')
		), $rrset->getOccurrences());
	}
}