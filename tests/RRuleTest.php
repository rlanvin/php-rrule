<?php

use RRule\RRule;

class RRuleTest extends PHPUnit_Framework_TestCase
{
	public function testMissingParameter()
	{
		$this->setExpectedException('InvalidArgumentException');
		new RRule([]);
	}

	public function testUnsupportedParameter()
	{
		$this->setExpectedException('InvalidArgumentException');
		new RRule([
			'FREQ' => 'DAILY',
			'FOO' => 'BAR'
		]);
	}

	public function validByMonth()
	{
		return array(
			['1'],
			['1,2'],
			[[1,2]]
		);
	}
	/**
	 * @dataProvider validByMonth
	 */
	public function testValidByMonth($bymonth)
	{
		new RRule([
			'FREQ' => 'DAILY',
			'BYMONTH' => $bymonth
		]);
	}

	public function invalidByMonth()
	{
		return array(
			[0],
			['-1'],
			[-1],
			[13]
		);
	}

	/**
	 * @dataProvider invalidByMonth
	 * @expectedException InvalidArgumentException
	 * @depends testValidByMonth
	 */
	public function testInvalidByMonth($bymonth)
	{
		new RRule([
			'FREQ' => 'DAILY',
			'BYMONTH' => $bymonth
		]);
	}


	public function validByDay()
	{
		return array(
			['MO'],
			['1MO'],
			['+1MO'],
			['-1MO'],
			['53MO'],
			['53MO']
		);
	}
	/**
	 * @dataProvider validByDay
	 */
	public function testValidByDay($byday)
	{
		new RRule([
			'FREQ' => 'DAILY',
			'BYDAY' => $byday
		]);
	}

	public function invalidByDay()
	{
		return array(
			[0],
			['54MO'],
			['-54MO']
		);
	}

	/**
	 * @dataProvider invalidByDay
	 * @expectedException InvalidArgumentException
	 * @depends testValidByDay
	 */
	public function testInvalidByDay($byday)
	{
		new RRule([
			'FREQ' => 'DAILY',
			'BYDAY' => $byday
		]);
	}


	public function testIsLeapYear()
	{
		$this->assertFalse(\RRule\is_leap_year(1700));
		$this->assertFalse(\RRule\is_leap_year(1800));
		$this->assertFalse(\RRule\is_leap_year(1900));
		$this->assertTrue(\RRule\is_leap_year(2000));
	}

// datetime\(([0-9]+), ([0-9]+), ([0-9]+)[ ,0-9\)]+

	public function yearlyRules()
	{
		return array(
			array([],['1997-09-02','1998-09-02','1999-09-02']),
			array(['INTERVAL' => 2], ['1997-09-02','1999-09-02','2001-09-02']),
			array(['BYMONTH' => [1,3]], ['1998-01-02','1998-03-02','1999-01-02']),
			array(['BYMONTHDAY' => [1,3]], ['1997-09-03','1997-10-01','1997-10-03']),
			array(['BYMONTH' => [1,3], 'BYMONTHDAY' => [5,7]], ['1998-01-05','1998-01-07','1998-03-05']),
			array(['BYDAY' => ['TU','TH']], ['1997-09-02','1997-09-04','1997-09-09']),
			array(['BYDAY' => ['SU']], ['1997-09-07','1997-09-14','1997-09-21']),
			array(['BYDAY' => ['1TU','-1TH']], ['1997-12-25','1998-01-06','1998-12-31']),
			array(['BYDAY' => ['3TU','-3TH']], ['1997-12-11','1998-01-20','1998-12-17']),
			array(['BYMONTH' => [1,3], 'BYDAY' => ['TU','TH']], ['1998-01-01','1998-01-06','1998-01-08']),
			array(['BYMONTH' => [1,3], 'BYDAY' => ['1TU','-1TH']], ['1998-01-06','1998-01-29','1998-03-03']),
			// This is interesting because the TH(-3) ends up before the TU(3).
			array(['BYMONTH' => [1,3], 'BYDAY' => ['3TU','-3TH']], ['1998-01-15','1998-01-20','1998-03-12']),
			array(['BYMONTHDAY' => [1,3], 'BYDAY' => ['TU','TH']], ['1998-01-01','1998-02-03','1998-03-03']),
			array(['BYMONTHDAY' => [1,3], 'BYDAY' => ['TU','TH'], 'BYMONTH' => [1,3]], ['1998-01-01','1998-03-03','2001-03-01']),
			array(['BYYEARDAY' => [1,100,200,365], 'COUNT' => 4], ['1997-12-31','1998-01-01','1998-04-10', '1998-07-19']),
			array(['BYYEARDAY' => [-365, -266, -166, -1], 'COUNT' => 4], ['1997-12-31','1998-01-01','1998-04-10', '1998-07-19']),
			array(['BYYEARDAY' => [1,100,200,365], 'BYMONTH' => [4,7], 'COUNT' => 4], ['1998-04-10','1998-07-19','1999-04-10', '1999-07-19']),
			array(['BYYEARDAY' => [-365, -266, -166, -1], 'BYMONTH' => [4,7], 'COUNT' => 4], ['1998-04-10','1998-07-19','1999-04-10', '1999-07-19']),
			// array(['BYWEEKNO' => 20],['1998-5-11','1998-5-12','1998-5-13']),
			// // That's a nice one. The first days of week number one may be in the last year.
			// array(['BYWEEKNO' => 1, 'BYDAY' => 'MO'], ['1997-12-29', '1999-01-04', '2000-01-03']),
			// // Another nice test. The last days of week number 52/53 may be in the next year.
			// array(['BYWEEKNO' => 52, 'BYDAY' => 'SU'], ['1997-12-28', '1998-12-27', '2000-01-02']),
			// array(['BYWEEKNO' => -1, 'BYDAY' => 'SU'], ['1997-12-28', '1999-01-03', '2000-01-02']),
			// array(['BYWEEKNO' => 53, 'BYDAY' => 'MO'], ['1998-12-28', '2004-12-27', '2009-12-28']),

			// FIXME (time part missing)
			// array(['BYHOUR' => [6, 18]], ['1997-09-02','1998-09-02','1998-09-02']),
			// array(['BYMINUTE'=> [6, 18]], ['1997-9-2', '1997-9-2', '1998-9-2']),
			// array(['BYSECOND' => [6, 18]], ['1997-9-2', '1997-9-2', '1998-9-2']),
			// array(['BYHOUR' => [6, 18], 'BYMINUTE' => [6, 18]],  ['1997-9-2','1997-9-2','1998-9-2']),
			// array(['BYHOUR' => [6, 18], 'BYSECOND' => [6, 18]], ['1997-9-2','1997-9-2','1998-9-2']),
			// array(['BYMINUTE' => [6, 18], 'BYSECOND' => [6, 18]], ['1997-9-2','1997-9-2','1997-9-2']),
			// array(['BYHOUR'=>[6, 18],'BYMINUTE'=>[6, 18],'BYSECOND'=>[6, 18]],['1997-9-2','1997-9-2','1997-9-2']),
			// array(['BYMONTHDAY'=>15,'BYHOUR'=>[6, 18],'BYSETPOS'=>[3, -3],['1997-11-15','1998-2-15','1998-11-15'])

		);
	}

	/**
	 * @dataProvider yearlyRules
	 */
	public function testYearly($rule, $occurrences)
	{
		$rule = new RRule(array_merge([
			'FREQ' => 'YEARLY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		], $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
	}


	public function monthlyRules()
	{
		return array(

		);
	}

	/**
	 * @dataProvider monthlyRules
	 */
	public function testMonthly($rule, $occurrences)
	{
		$rule = new RRule(array_merge([
			'FREQ' => 'MONTHLY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		], $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
	}
}