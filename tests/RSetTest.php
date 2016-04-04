<?php

use RRule\RSet;
use RRule\RRule;

class RSetTest extends PHPUnit_Framework_TestCase
{
	public function testCombineRRule()
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

		$this->assertEquals(date_create('1997-09-04 09:00'),$rset[1]);
		$this->assertEquals(array(date_create('1997-09-04 09:00')),$rset->getOccurrencesBetween('1997-09-04 00:00', '1997-09-05 00:00'));

		$this->assertTrue($rset->occursAt('1997-09-02 09:00'));
		$this->assertFalse($rset->occursAt('1997-09-03 09:00'));

		$rset->clearCache();

		$this->assertTrue($rset->occursAt('1997-09-02 09:00'));
		$this->assertFalse($rset->occursAt('1997-09-03 09:00'));
	}

	public function testCombineRDate()
	{
		$rset =new RSet();
		$rset->addDate(date_create('1997-09-09 09:00')); // adding out of order
		$rset->addDate('1997-09-04 09:00');
		$rset->addDate('1997-09-04 09:00'); // adding a duplicate

		$this->assertEquals(array(
			date_create('1997-09-04 09:00'),
			date_create('1997-09-09 09:00')
		), $rset->getOccurrences(), 'occurrences are ordered and deduplicated');
	}

	public function testCombineRRuleAndDate()
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

		$this->assertEquals(date_create('1997-09-04 09:00'),$rset[1]);
		$this->assertEquals(array(date_create('1997-09-04 09:00')),$rset->getOccurrencesBetween('1997-09-04 00:00', '1997-09-05 00:00'));

		$this->assertTrue($rset->occursAt('1997-09-04 09:00'));
		$this->assertFalse($rset->occursAt('1997-09-03 09:00'));

		$rset->clearCache();

		$this->assertTrue($rset->occursAt('1997-09-04 09:00'));
		$this->assertFalse($rset->occursAt('1997-09-03 09:00'));
	}

	public function testCombineRRuleAndExRule()
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

		$this->assertEquals(date_create('1997-09-09 09:00'),$rset[1]);
		$this->assertEquals(array(date_create('1997-09-16 09:00')),$rset->getOccurrencesBetween('1997-09-16 00:00', '1997-09-17 00:00'));

		$this->assertTrue($rset->occursAt('1997-09-09 09:00'));
		$this->assertFalse($rset->occursAt('1997-09-04 09:00'));

		$rset->clearCache();

		$this->assertTrue($rset->occursAt('1997-09-09 09:00'));
		$this->assertFalse($rset->occursAt('1997-09-04 09:00'));
	}

	public function testCombineRRuleAndExDate()
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
		$rset->addExdate('1997-09-18 09:00:00'); // adding out of order

		$this->assertEquals(array(
			date_create('1997-09-02 09:00'),
			date_create('1997-09-09 09:00'),
			date_create('1997-09-16 09:00')
		), $rset->getOccurrences());

		$this->assertEquals(date_create('1997-09-09 09:00'),$rset[1]);
		$this->assertEquals(array(date_create('1997-09-16 09:00')),$rset->getOccurrencesBetween('1997-09-16 00:00', '1997-09-17 00:00'));

		$this->assertTrue($rset->occursAt('1997-09-02 09:00'));
		$this->assertFalse($rset->occursAt('1997-09-04 09:00'));

		$rset->clearCache();

		$this->assertTrue($rset->occursAt('1997-09-02 09:00'));
		$this->assertFalse($rset->occursAt('1997-09-04 09:00'));
	}

	public function testCombineEverything()
	{
		// TODO
	}

	public function testCombineMultipleTimezones()
	{
		$rset = new RSet();
		$rset->addRRule(array(
			'FREQ' => 'DAILY',
			'COUNT' => 2,
			'DTSTART' => date_create('2000-01-02 09:00', new DateTimeZone('Europe/Paris'))
		));
		$rset->addRRule(array(
			'FREQ' => 'DAILY',
			'COUNT' => 2,
			'DTSTART' => date_create('2000-01-02 09:00', new DateTimeZone('Europe/Helsinki'))
		));

		$this->assertEquals(array(
			date_create('2000-01-02 09:00', new DateTimeZone('Europe/Helsinki')),
			date_create('2000-01-02 09:00', new DateTimeZone('Europe/Paris')),
			date_create('2000-01-03 09:00', new DateTimeZone('Europe/Helsinki')),
			date_create('2000-01-03 09:00', new DateTimeZone('Europe/Paris'))
		), $rset->getOccurrences());
	}

///////////////////////////////////////////////////////////////////////////////
// Other tests

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

	public function testModifyResetCache()
	{
		$rset = new RSet();
		$rset->addRRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 6,
			'BYDAY' => 'TU,TH',
			'DTSTART' => date_create('1997-09-02 09:00')
		));
		$this->assertEquals(array(
			date_create('1997-09-02 09:00'),
			date_create('1997-09-04 09:00'),
			date_create('1997-09-09 09:00'),
			date_create('1997-09-11 09:00'),
			date_create('1997-09-16 09:00'),
			date_create('1997-09-18 09:00')
		), $rset->getOccurrences());

		$r = new ReflectionObject($rset);
		$cache = $r->getProperty('cache');
		$cache->setAccessible('true');
		$this->assertNotEmpty($cache->getValue($rset), 'Cache is not empty');

		$rset->addExRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 3,
			'BYDAY' => 'TH',
			'DTSTART' => date_create('1997-09-02 09:00')
		));
		$this->assertEmpty($cache->getValue($rset), 'Cache has been emptied by addExRule');

		$this->assertEquals(array(
			date_create('1997-09-02 09:00'),
			date_create('1997-09-09 09:00'),
			date_create('1997-09-16 09:00')
		), $rset->getOccurrences(), 'Iteration works');
	}

	public function testPartialCache()
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

		foreach ( $rset as $occurrence ) {
			$this->assertEquals(date_create('1997-09-02 09:00'), $occurrence);
			break;
		}

		$r = new ReflectionObject($rset);
		$cache = $r->getProperty('cache');
		$cache->setAccessible('true');
		$this->assertNotEmpty($cache->getValue($rset), 'Cache is not empty (partially filled)');

		$this->assertEquals(date_create('1997-09-02 09:00'), $rset[0], 'Partial cache is returned');
		$this->assertEquals(date_create('1997-09-09 09:00'), $rset[1], 'Next occurrence is calculated correctly');

		$this->assertEquals(array(
			date_create('1997-09-02 09:00'),
			date_create('1997-09-09 09:00'),
			date_create('1997-09-16 09:00')
		), $rset->getOccurrences(), 'Iteration works');
	}

	public function testCountable()
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

		$this->assertEquals(3, count($rset));
	}

	public function testOffsetExists()
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

		$this->assertTrue(isset($rset[0]));
		$this->assertTrue(isset($rset[1]));
		$this->assertTrue(isset($rset[2]));
		$this->assertFalse(isset($rset[3]));
	}

	public function testOffsetGet()
	{
		$rset = new RSet();
		$rset->addRRule(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 6,
			'BYDAY' => 'TU, TH',
			'DTSTART' => date_create('1997-09-02 09:00:00')
		));
		$rset->addExdate('1997-09-04 09:00:00');
		$rset->addExdate('1997-09-11 09:00:00');
		$rset->addExdate('1997-09-18 09:00:00');

		$this->assertEquals(date_create('1997-09-02 09:00:00'), $rset[0]);
		$this->assertEquals(date_create('1997-09-09 09:00:00'), $rset[1]);
		$this->assertEquals(date_create('1997-09-16 09:00:00'), $rset[2]);
		$this->assertEquals(null, $rset[3]);
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