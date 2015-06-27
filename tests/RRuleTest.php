<?php

use RRule\RRule;

class RRuleTest extends PHPUnit_Framework_TestCase
{
	public function invalidRules()
	{
		return array(
			array(array()),
			array(array('FREQ' => 'foobar')),
			array(array('FREQ' => 'DAILY', 'INTERVAL' => -1)),
			array(array('FREQ' => 'DAILY', 'UNTIL' => 'foobar')),
			array(array('FREQ' => 'DAILY', 'COUNT' => -1)),

			// The BYDAY rule part MUST NOT be specified with a numeric value
			// when the FREQ rule part is not set to MONTHLY or YEARLY.
			array(array('FREQ' => 'DAILY', 'BYDAY' => array('1MO'))),
			array(array('FREQ' => 'WEEKLY', 'BYDAY' => array('1MO'))),
			// The BYDAY rule part MUST NOT be specified with a numeric value
			// with the FREQ rule part set to YEARLY when the BYWEEKNO rule part is specified.
			array(array('FREQ' => 'YEARLY', 'BYDAY' => array('1MO'), 'BYWEEKNO' => 20)),

			array(array('FREQ' => 'DAILY', 'BYMONTHDAY' => 0)),
			array(array('FREQ' => 'DAILY', 'BYMONTHDAY' => 32)),
			array(array('FREQ' => 'DAILY', 'BYMONTHDAY' => -32)),
			// The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule
			// part is set to WEEKLY.
			array(array('FREQ' => 'WEEKLY', 'BYMONTHDAY' => 1)),

			array(array('FREQ' => 'YEARLY', 'BYYEARDAY' => 0)),
			array(array('FREQ' => 'YEARLY', 'BYYEARDAY' => 367)),
			// The BYYEARDAY rule part MUST NOT be specified when the FREQ
			// rule part is set to DAILY, WEEKLY, or MONTHLY.
			array(array('FREQ' => 'DAILY', 'BYYEARDAY' => 1)),
			array(array('FREQ' => 'WEEKLY', 'BYYEARDAY' => 1)),
			array(array('FREQ' => 'MONTHLY', 'BYYEARDAY' => 1)),

			// BYSETPOS rule part MUST only be used in conjunction with another
			// BYxxx rule part.
			array(array('FREQ' => 'DAILY', 'BYSETPOS' => -1)),
		);
	}

	/**
	 * @dataProvider invalidRules
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidRules($rule)
	{
		new RRule($rule);
	}

	public function testIsLeapYear()
	{
		$this->assertFalse(\RRule\is_leap_year(1700));
		$this->assertFalse(\RRule\is_leap_year(1800));
		$this->assertFalse(\RRule\is_leap_year(1900));
		$this->assertTrue(\RRule\is_leap_year(2000));
	}

// date_create\((array(0-9)+), (array(0-9)+), (array(0-9)+)array( ,0-9\))+

	public function yearlyRules()
	{
		return array(
			array(array(),array(date_create('1997-09-02'),date_create('1998-09-02'), date_create('1999-09-02'))),
			array(array('INTERVAL' => 2), array(date_create('1997-09-02'),date_create('1999-09-02'),date_create('2001-09-02'))),
			array(array('DTSTART' => '2000-02-29'), array(date_create('2000-02-29'),date_create('2004-02-29'),date_create('2008-02-29'))),
			array(array('BYMONTH' => array(1,3)), array(date_create('1998-01-02'),date_create('1998-03-02'),date_create('1999-01-02'))),
			array(array('BYMONTHDAY' => array(1,3)), array(date_create('1997-09-03'),date_create('1997-10-01'),date_create('1997-10-03'))),
			array(array('BYMONTH' => array(1,3), 'BYMONTHDAY' => array(5,7)), array(date_create('1998-01-05'),date_create('1998-01-07'),date_create('1998-03-05'))),
			array(array('BYDAY' => array('TU','TH')), array(date_create('1997-09-02'),date_create('1997-09-04'),date_create('1997-09-09'))),
			array(array('BYDAY' => array('SU')), array(date_create('1997-09-07'),date_create('1997-09-14'),date_create('1997-09-21'))),
			array(array('BYDAY' => array('1TU','-1TH')), array(date_create('1997-12-25'),date_create('1998-01-06'),date_create('1998-12-31'))),
			array(array('BYDAY' => array('3TU','-3TH')), array(date_create('1997-12-11'),date_create('1998-01-20'),date_create('1998-12-17'))),
			array(array('BYMONTH' => array(1,3), 'BYDAY' => array('TU','TH')), array(date_create('1998-01-01'),date_create('1998-01-06'),date_create('1998-01-08'))),
			array(array('BYMONTH' => array(1,3), 'BYDAY' => array('1TU','-1TH')), array(date_create('1998-01-06'),date_create('1998-01-29'),date_create('1998-03-03'))),
			// This is interesting because the TH(-3) ends up before the TU(3).
			array(array('BYMONTH' => array(1,3), 'BYDAY' => array('3TU','-3TH')), array(date_create('1998-01-15'),date_create('1998-01-20'),date_create('1998-03-12'))),
			array(array('BYMONTHDAY' => array(1,3), 'BYDAY' => array('TU','TH')), array(date_create('1998-01-01'),date_create('1998-02-03'),date_create('1998-03-03'))),
			array(array('BYMONTHDAY' => array(1,3), 'BYDAY' => array('TU','TH'), 'BYMONTH' => array(1,3)), array(date_create('1998-01-01'),date_create('1998-03-03'),date_create('2001-03-01'))),
			array(array('BYYEARDAY' => array(1,100,200,365), 'COUNT' => 4), array(date_create('1997-12-31'),date_create('1998-01-01'),date_create('1998-04-10'), date_create('1998-07-19'))),
			array(array('BYYEARDAY' => array(-365, -266, -166, -1), 'COUNT' => 4), array(date_create('1997-12-31'),date_create('1998-01-01'),date_create('1998-04-10'), date_create('1998-07-19'))),
			array(array('BYYEARDAY' => array(1,100,200,365), 'BYMONTH' => array(4,7), 'COUNT' => 4), array(date_create('1998-04-10'),date_create('1998-07-19'),date_create('1999-04-10'), date_create('1999-07-19'))),
			array(array('BYYEARDAY' => array(-365, -266, -166, -1), 'BYMONTH' => array(4,7), 'COUNT' => 4), array(date_create('1998-04-10'),date_create('1998-07-19'),date_create('1999-04-10'), date_create('1999-07-19'))),
			array(array('BYWEEKNO' => 20),array(date_create('1998-05-11'),date_create('1998-05-12'),date_create('1998-05-13'))),
			// That's a nice one. The first days of week number one may be in the last year.
			array(array('BYWEEKNO' => 1, 'BYDAY' => 'MO'), array(date_create('1997-12-29'), date_create('1999-01-04'), date_create('2000-01-03'))),
			// Another nice test. The last days of week number 52/53 may be in the next year.
			array(array('BYWEEKNO' => 52, 'BYDAY' => 'SU'), array(date_create('1997-12-28'), date_create('1998-12-27'), date_create('2000-01-02'))),
			array(array('BYWEEKNO' => -1, 'BYDAY' => 'SU'), array(date_create('1997-12-28'), date_create('1999-01-03'), date_create('2000-01-02'))),
			array(array('BYWEEKNO' => 53, 'BYDAY' => 'MO'), array(date_create('1998-12-28'), date_create('2004-12-27'), date_create('2009-12-28'))),

			// FIXME (time part missing)
			// array(array('BYHOUR' => array(6, 18)), array(date_create('1997-09-02'),date_create('1998-09-02'),date_create('1998-09-02'))),
			// array(array('BYMINUTE'=> array(6, 18)), array('1997-09-02', '1997-09-02', '1998-09-02')),
			// array(array('BYSECOND' => array(6, 18)), array('1997-09-02', '1997-09-02', '1998-09-02')),
			// array(array('BYHOUR' => array(6, 18), 'BYMINUTE' => array(6, 18)),  array('1997-09-02','1997-09-02','1998-09-02')),
			// array(array('BYHOUR' => array(6, 18), 'BYSECOND' => array(6, 18)), array('1997-09-02','1997-09-02','1998-09-02')),
			// array(array('BYMINUTE' => array(6, 18), 'BYSECOND' => array(6, 18)), array('1997-09-02','1997-09-02','1997-09-02')),
			// array(array('BYHOUR'=>array(6, 18),'BYMINUTE'=>array(6, 18),'BYSECOND'=>array(6, 18)),array('1997-09-02','1997-09-02','1997-09-02')),
			// array(array('BYMONTHDAY'=>15,'BYHOUR'=>array(6, 18),'BYSETPOS'=>array(3, -3),array(date_create('1997-11-15'),date_create('1998-02-15'),date_create('1998-11-15')))
		);
	}

	/**
	 * @dataProvider yearlyRules
	 */
	public function testYearly($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'YEARLY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		foreach ( $occurrences as $date ) {
			$this->assertTrue($rule->occursAt($date), $date->format('r'));
		}
	}


	public function monthlyRules()
	{
		return array(
			array(array(),array(date_create('1997-09-02'),date_create('1997-10-02'),date_create('1997-11-02'))),
			array(array('INTERVAL'=>2),array(date_create('1997-09-02'),date_create('1997-11-02'),date_create('1998-01-02'))),
			array(array('INTERVAL'=>18),array(date_create('1997-09-02'),date_create('1999-03-02'),date_create('2000-09-02'))),
			array(array('BYMONTH' => array(1, 3)),array(date_create('1998-01-02'),date_create('1998-03-02'),date_create('1999-01-02'))),
			array(array('BYMONTHDAY' => array(1, 3)),array(date_create('1997-09-03'),date_create('1997-10-01'),date_create('1997-10-03'))),
			array(array('BYMONTHDAY' => array(5, 7), 'BYMONTH' => array(1, 3)), array(date_create('1998-01-05'), date_create('1998-01-07'), date_create('1998-03-05'))),
			array(array('BYDAY' => array('TU', 'TH')), array(date_create('1997-09-02'),date_create('1997-09-04'),date_create('1997-09-09'))),
			// Third Monday of the month
			array(array('BYDAY' => '3MO'),array(date_create('1997-09-15'),date_create('1997-10-20'),date_create('1997-11-17'))),
			array(array('BYDAY' => '1TU,-1TH'),array(date_create('1997-09-02'),date_create('1997-09-25'),date_create('1997-10-07'))),
			array(array('BYDAY' => '3TU,-3TH'),array(date_create('1997-09-11'),date_create('1997-09-16'),date_create('1997-10-16'))),
			array(array('BYDAY' => 'TU,TH', 'BYMONTH' => array(1, 3)),array(date_create('1998-01-01'),date_create('1998-01-06'),date_create('1998-01-08'))),
			array(array('BYMONTH' => array(1, 3), 'BYDAY' => '1TU, -1TH'),array(date_create('1998-01-06'),date_create('1998-01-29'),date_create('1998-03-03'))),
			array(array('BYMONTH' => array(1, 3), 'BYDAY' => '3TU, -3TH'),array(date_create('1998-01-15'),date_create('1998-01-20'),date_create('1998-03-12'))),
			array(array('BYMONTHDAY' => array(1, 3), 'BYDAY' => array('TU', 'TH')), array(date_create('1998-01-01'),date_create('1998-02-03'),date_create('1998-03-03'))),
			array(array('BYMONTH' => array(1, 3), 'BYMONTHDAY' => array(1, 3), 'BYDAY' => array('TU', 'TH')),array(date_create('1998-01-01'),date_create('1998-03-03'),date_create('2001-03-01'))),

			// array(array('BYHOUR'=> array(6, 18),array('1997-09-02',date_create('1997-10-02'),date_create('1997-10-02'))),
			// array(array('BYMINUTE'=> array(6, 18),array('1997-09-02','1997-09-02',date_create('1997-10-02'))),
			// array(array('BYSECOND' => array(6, 18),array('1997-09-02','1997-09-02',date_create('1997-10-02'))),
			// array(array('BYHOUR'=>array(6, 18),'BYMINUTE'=>array(6, 18)),array('1997-09-02','1997-09-02',date_create('1997-10-02'))),
			// array(array('BYHOUR'=>array(6, 18),'BYSECOND'=>array(6, 18)),array('1997-09-02','1997-09-02',date_create('1997-10-02'))),
			// array(array('BYMINUTE'=>array(6, 18),'BYSECOND'=>array(6, 18)),array('1997-09-02','1997-09-02','1997-09-02')),
			// array(array('BYHOUR'=>array(6, 18),'BYMINUTE'=>array(6, 18),'BYSECOND'=>array(6, 18)),array('1997-09-02','1997-09-02','1997-09-02')),
			// array(array('BYMONTHDAY'=>array(13, 17),'BYHOUR'=>array(6, 18),'BYSETPOS'=>array(3, -3)),array(date_create('1997-09-13'),date_create('1997-09-17'),date_create('1997-10-13')))
		);
	}

	/**
	 * @dataProvider monthlyRules
	 */
	public function testMonthly($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'MONTHLY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		foreach ( $occurrences as $date ) {
			$this->assertTrue($rule->occursAt($date), $date->format('r'));
		}
	}

	public function weeklyRules()
	{
		return array(
			array(array(),array(date_create('1997-09-02'), date_create('1997-09-09'), date_create('1997-09-16'))),
			array(array('interval'=>2),array(date_create('1997-09-02'),date_create('1997-09-16'),date_create('1997-09-30'))),
			array(array('interval'=>20),array(date_create('1997-09-02'),date_create('1998-01-20'),date_create('1998-06-09'))),
			array(array('bymonth'=>array(1, 3)),array(date_create('1998-01-06'),date_create('1998-01-13'),date_create('1998-01-20'))),
			array(array('byday'=> array('TU', 'TH')),array(date_create('1997-09-02'), date_create('1997-09-04'), date_create('1997-09-09'))),

			# This test is interesting, because it crosses the year
			# boundary in a weekly period to find day '1' as a
			# valid recurrence.
			array(array('bymonth'=>array(1, 3),'byday'=>array('TU', 'TH')),array(date_create('1998-01-01'), date_create('1998-01-06'), date_create('1998-01-08'))),

			// array(array('byhour'=>array(6, 18)),array(date_create('1997-09-02'),date_create('1997-09-09'),date_create('1997-09-09'))),
			// array(array('byminute'=>array(6, 18)),array(date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-09'))),
			// array(array('bysecond'=> array(6, 18)),array(date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-09'))),
			// array(array('byhour'=> array(6, 18),'byminute'=>array(6, 18)),array(date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-09'))),
			// array(array('byhour'=>array(6, 18),'bysecond'=>array(6, 18)),array(date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-09'))),
			// array(array('byminute'=>array(6, 18),'bysecond'=>array(6, 18)),array(date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-02'))),
			// array(array('byhour'=>array(6, 18),'byminute'=>array(6, 18),'bysecond'=>array(6, 18)),array(date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-02'))),
			// array(array('byday'=>array('TU', 'TH'),'byhour'=>array(6, 18),'bysetpos'=>array(3, -3)),array(date_create('1997-09-02'),date_create('1997-09-04'),date_create('1997-09-09')))
		);
	}
	/**
	 * @dataProvider weeklyRules
	 */
	public function testWeekly($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'WEEKLY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		foreach ( $occurrences as $date ) {
			$this->assertTrue($rule->occursAt($date), $date->format('r'));
		}
	}

	public function dailyRules()
	{
		return array(
			array(array(), array(date_create('1997-09-02'),date_create('1997-09-03'),date_create('1997-09-04'))),
			array(array('interval'=>2),array(date_create('1997-09-02'), date_create('1997-09-04'), date_create('1997-09-06'))),
			array(array('interval'=>92),array(date_create('1997-09-02'), date_create('1997-12-03'), date_create('1998-03-05'))),
			array(array('bymonth'=>array(1, 3)),array(date_create('1998-01-01'), date_create('1998-01-02'), date_create('1998-01-03'))),
			array(array('bymonthday'=>array(1, 3)),array(date_create('1997-09-03'), date_create('1997-10-01'), date_create('1997-10-03'))),
			array(array('bymonth'=>array(1, 3),'bymonthday'=>array(5, 7)),array(date_create('1998-01-05'), date_create('1998-01-07'), date_create('1998-03-05'))),
			array(array('byday'=>array('TU', 'TH')),array(date_create('1997-09-02'), date_create('1997-09-04'), date_create('1997-09-09'))),
			array(array('bymonth'=> array(1, 3), 'byday'=> array('TU', 'TH')),array(date_create('1998-01-01'), date_create('1998-01-06'), date_create('1998-01-08'))),
			array(array('bymonthday'=> array(1, 3), 'byday'=>array('TU', 'TH')),array(date_create('1998-01-01'), date_create('1998-02-03'), date_create('1998-03-03'))),
			array(array('bymonth'=>array(1, 3),'bymonthday'=>array(1, 3),'byday'=>array('TU', 'TH')),array(date_create('1998-01-01'), date_create('1998-03-03'), date_create('2001-03-01'))),
			// array(array('count'=>4,'byyearday'=>array(1, 100, 200, 365)),array(date_create('1997-12-31'), date_create('1998-01-01'), date_create('1998-04-10'), date_create('1998-07-19'))),
			// array(array('count'=>4,'byyearday'=>array(-365, -266, -166, -1)),array(date_create('1997-12-31'), date_create('1998-01-01'), date_create('1998-04-10'), date_create('1998-07-19'))),
			// array(array('count'=>4, 'bymonth'=>array(1, 7),'byyearday'=>array(1, 100, 200, 365)),array(date_create('1998-01-01'),date_create('1998-07-19'),date_create('1999-01-01'),date_create('1999-07-19'))),
			// array(array('count'=>4, 'bymonth' => array(1, 7), 'byyearday' => array(-365, -266, -166, -1)),array(date_create('1998-01-01'), date_create('1998-07-19'), date_create('1999-01-01'), date_create('1999-07-19'))),
			// array(array('byweekno' => 20), array(date_create('1998-05-11'), date_create('1998-05-12'), date_create('1998-05-13'))),
			// array(array('byweekno' => 1, 'byday' => 'MO'),array(date_create('1997-12-29'),date_create('1999-01-04'),date_create('2000-01-03'))),
			// array(array('byweekno' => 52, 'byday' => 'SU'), array(date_create('1997-12-28'),  date_create('1998-12-27'), date_create('2000-01-02'))),
			// array(array('byweekno' => -1, 'byday' => 'SU'),array(date_create('1997-12-28'),date_create('1999-01-03'),date_create('2000-01-02'))),
			// array(array('byweekno'=>53,'byday'=>'MO'),array(date_create('1998-12-28'), date_create('2004-12-27'), date_create('2009-12-28')))
		);
	}
	/**
	 * @dataProvider dailyRules
	 */
	public function testDaily($rule, $occurrences)
	{
		$rule = new RRule(array_merge(array(
			'FREQ' => 'DAILY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		), $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
		foreach ( $occurrences as $date ) {
			$this->assertTrue($rule->occursAt($date), $date->format('r'));
		}
	}


//     def testDailyByHour(self):
//         self.assertEqual(list(rrule(DAILY,

//      byhour=(6, 18),

// array(date_create('1997-09-02'),
//  date_create('1997-09-03'),
//  date_create('1997-09-03')))

//     def testDailyByMinute(self):
//         self.assertEqual(list(rrule(DAILY,

//      byminute=(6, 18),

// array(date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-03')))

//     def testDailyBySecond(self):
//         self.assertEqual(list(rrule(DAILY,

//      bysecond=(6, 18),

// array(date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-03')))

//     def testDailyByHourAndMinute(self):
//         self.assertEqual(list(rrule(DAILY,

//      byhour=(6, 18),
//      byminute=(6, 18),

// array(date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-03')))

//     def testDailyByHourAndSecond(self):
//         self.assertEqual(list(rrule(DAILY,

//      byhour=(6, 18),
//      bysecond=(6, 18),

// array(date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-03')))

//     def testDailyByMinuteAndSecond(self):
//         self.assertEqual(list(rrule(DAILY,

//      byminute=(6, 18),
//      bysecond=(6, 18),

// array(date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-02')))

//     def testDailyByHourAndMinuteAndSecond(self):
//         self.assertEqual(list(rrule(DAILY,

//      byhour=(6, 18),
//      byminute=(6, 18),
//      bysecond=(6, 18),

// array(date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-02')))

//     def testDailyBySetPos(self):
//         self.assertEqual(list(rrule(DAILY,

//      byhour=(6, 18),
//      byminute=(15, 45),
//      bysetpos=(3, -3),

// array(date_create('1997-09-02'),
//  date_create('1997-09-03'),
//  date_create('1997-09-03')))

	/**
	 * Examples given in the RFC.
	 */
	public function rfcExamples()
	{
		return array(
			// Daily, for 10 occurrences.
			array(
				array('freq' => 'daily', 'count' => 10, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-03 09:00:00'),
				date_create('1997-09-04 09:00:00'),
				date_create('1997-09-05 09:00:00'),
				date_create('1997-09-06 09:00:00'),
				date_create('1997-09-07 09:00:00'),
				date_create('1997-09-08 09:00:00'),
				date_create('1997-09-09 09:00:00'),
				date_create('1997-09-10 09:00:00'),
				date_create('1997-09-11 09:00:00'))
			),
			// Daily until December 24, 1997
			array(
				array('freq' => 'daily', 'dtstart' => '1997-09-02 09:00:00', 'until' => '1997-12-24 00:00:00'),
				array(date_create('1997-09-02 09:00:00'), date_create('1997-09-03 09:00:00'),
				date_create('1997-09-04 09:00:00'), date_create('1997-09-05 09:00:00'),
				date_create('1997-09-06 09:00:00'), date_create('1997-09-07 09:00:00'),
				date_create('1997-09-08 09:00:00'), date_create('1997-09-09 09:00:00'),
				date_create('1997-09-10 09:00:00'), date_create('1997-09-11 09:00:00'),
				date_create('1997-09-12 09:00:00'), date_create('1997-09-13 09:00:00'),
				date_create('1997-09-14 09:00:00'), date_create('1997-09-15 09:00:00'),
				date_create('1997-09-16 09:00:00'), date_create('1997-09-17 09:00:00'),
				date_create('1997-09-18 09:00:00'), date_create('1997-09-19 09:00:00'),
				date_create('1997-09-20 09:00:00'), date_create('1997-09-21 09:00:00'),
				date_create('1997-09-22 09:00:00'), date_create('1997-09-23 09:00:00'),
				date_create('1997-09-24 09:00:00'), date_create('1997-09-25 09:00:00'),
				date_create('1997-09-26 09:00:00'), date_create('1997-09-27 09:00:00'),
				date_create('1997-09-28 09:00:00'), date_create('1997-09-29 09:00:00'),
				date_create('1997-09-30 09:00:00'), date_create('1997-10-01 09:00:00'),
				date_create('1997-10-02 09:00:00'), date_create('1997-10-03 09:00:00'),
				date_create('1997-10-04 09:00:00'), date_create('1997-10-05 09:00:00'),
				date_create('1997-10-06 09:00:00'), date_create('1997-10-07 09:00:00'),
				date_create('1997-10-08 09:00:00'), date_create('1997-10-09 09:00:00'),
				date_create('1997-10-10 09:00:00'), date_create('1997-10-11 09:00:00'),
				date_create('1997-10-12 09:00:00'), date_create('1997-10-13 09:00:00'),
				date_create('1997-10-14 09:00:00'), date_create('1997-10-15 09:00:00'),
				date_create('1997-10-16 09:00:00'), date_create('1997-10-17 09:00:00'),
				date_create('1997-10-18 09:00:00'), date_create('1997-10-19 09:00:00'),
				date_create('1997-10-20 09:00:00'), date_create('1997-10-21 09:00:00'),
				date_create('1997-10-22 09:00:00'), date_create('1997-10-23 09:00:00'),
				date_create('1997-10-24 09:00:00'), date_create('1997-10-25 09:00:00'),
				date_create('1997-10-26 09:00:00'), date_create('1997-10-27 09:00:00'),
				date_create('1997-10-28 09:00:00'), date_create('1997-10-29 09:00:00'),
				date_create('1997-10-30 09:00:00'), date_create('1997-10-31 09:00:00'),
				date_create('1997-11-01 09:00:00'),
				date_create('1997-11-02 09:00:00'), date_create('1997-11-03 09:00:00'),
				date_create('1997-11-04 09:00:00'), date_create('1997-11-05 09:00:00'),
				date_create('1997-11-06 09:00:00'), date_create('1997-11-07 09:00:00'),
				date_create('1997-11-08 09:00:00'), date_create('1997-11-09 09:00:00'),
				date_create('1997-11-10 09:00:00'), date_create('1997-11-11 09:00:00'),
				date_create('1997-11-12 09:00:00'), date_create('1997-11-13 09:00:00'),
				date_create('1997-11-14 09:00:00'), date_create('1997-11-15 09:00:00'),
				date_create('1997-11-16 09:00:00'), date_create('1997-11-17 09:00:00'),
				date_create('1997-11-18 09:00:00'), date_create('1997-11-19 09:00:00'),
				date_create('1997-11-20 09:00:00'), date_create('1997-11-21 09:00:00'),
				date_create('1997-11-22 09:00:00'), date_create('1997-11-23 09:00:00'),
				date_create('1997-11-24 09:00:00'), date_create('1997-11-25 09:00:00'),
				date_create('1997-11-26 09:00:00'), date_create('1997-11-27 09:00:00'),
				date_create('1997-11-28 09:00:00'), date_create('1997-11-29 09:00:00'),
				date_create('1997-11-30 09:00:00'), date_create('1997-12-01 09:00:00'),
				date_create('1997-12-02 09:00:00'), date_create('1997-12-03 09:00:00'),
				date_create('1997-12-04 09:00:00'), date_create('1997-12-05 09:00:00'),
				date_create('1997-12-06 09:00:00'), date_create('1997-12-07 09:00:00'),
				date_create('1997-12-08 09:00:00'), date_create('1997-12-09 09:00:00'),
				date_create('1997-12-10 09:00:00'), date_create('1997-12-11 09:00:00'),
				date_create('1997-12-12 09:00:00'), date_create('1997-12-13 09:00:00'),
				date_create('1997-12-14 09:00:00'), date_create('1997-12-15 09:00:00'),
				date_create('1997-12-16 09:00:00'), date_create('1997-12-17 09:00:00'),
				date_create('1997-12-18 09:00:00'), date_create('1997-12-19 09:00:00'),
				date_create('1997-12-20 09:00:00'), date_create('1997-12-21 09:00:00'),
				date_create('1997-12-22 09:00:00'), date_create('1997-12-23 09:00:00'))
			),
			// Every other day, 5 occurrences.
			array(
				array('freq' => 'daily', 'interval' => 2, 'count' => 5, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				 date_create('1997-09-04 09:00:00'),
				 date_create('1997-09-06 09:00:00'),
				 date_create('1997-09-08 09:00:00'),
				 date_create('1997-09-10 09:00:00'))
			),
			// Every 10 days, 5 occurrences.
			array(
				array('freq' => 'daily', 'interval' => 10, 'count' => 5, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				 date_create('1997-09-12 09:00:00'),
				 date_create('1997-09-22 09:00:00'),
				 date_create('1997-10-02 09:00:00'),
				 date_create('1997-10-12 09:00:00'))
			),
			// Everyday in January, for 3 years.
			array(
				array('freq' => 'yearly', 'bymonth' => 1, 'byday' => 'MO,TU,WE,TH,FR,SA,SU', 'dtstart' => '1997-09-02 09:00:00', 'until' => '2000-01-31 09:00:00'),
				array(date_create('1998-01-01 09:00:00'),
				date_create('1998-01-02 09:00:00'), date_create('1998-01-03 09:00:00'),
				date_create('1998-01-04 09:00:00'), date_create('1998-01-05 09:00:00'),
				date_create('1998-01-06 09:00:00'), date_create('1998-01-07 09:00:00'),
				date_create('1998-01-08 09:00:00'), date_create('1998-01-09 09:00:00'),
				date_create('1998-01-10 09:00:00'), date_create('1998-01-11 09:00:00'),
				date_create('1998-01-12 09:00:00'), date_create('1998-01-13 09:00:00'),
				date_create('1998-01-14 09:00:00'), date_create('1998-01-15 09:00:00'),
				date_create('1998-01-16 09:00:00'), date_create('1998-01-17 09:00:00'),
				date_create('1998-01-18 09:00:00'), date_create('1998-01-19 09:00:00'),
				date_create('1998-01-20 09:00:00'), date_create('1998-01-21 09:00:00'),
				date_create('1998-01-22 09:00:00'), date_create('1998-01-23 09:00:00'),
				date_create('1998-01-24 09:00:00'), date_create('1998-01-25 09:00:00'),
				date_create('1998-01-26 09:00:00'), date_create('1998-01-27 09:00:00'),
				date_create('1998-01-28 09:00:00'), date_create('1998-01-29 09:00:00'),
				date_create('1998-01-30 09:00:00'), date_create('1998-01-31 09:00:00'),
				date_create('1999-01-01 09:00:00'),
				date_create('1999-01-02 09:00:00'), date_create('1999-01-03 09:00:00'),
				date_create('1999-01-04 09:00:00'), date_create('1999-01-05 09:00:00'),
				date_create('1999-01-06 09:00:00'), date_create('1999-01-07 09:00:00'),
				date_create('1999-01-08 09:00:00'), date_create('1999-01-09 09:00:00'),
				date_create('1999-01-10 09:00:00'), date_create('1999-01-11 09:00:00'),
				date_create('1999-01-12 09:00:00'), date_create('1999-01-13 09:00:00'),
				date_create('1999-01-14 09:00:00'), date_create('1999-01-15 09:00:00'),
				date_create('1999-01-16 09:00:00'), date_create('1999-01-17 09:00:00'),
				date_create('1999-01-18 09:00:00'), date_create('1999-01-19 09:00:00'),
				date_create('1999-01-20 09:00:00'), date_create('1999-01-21 09:00:00'),
				date_create('1999-01-22 09:00:00'), date_create('1999-01-23 09:00:00'),
				date_create('1999-01-24 09:00:00'), date_create('1999-01-25 09:00:00'),
				date_create('1999-01-26 09:00:00'), date_create('1999-01-27 09:00:00'),
				date_create('1999-01-28 09:00:00'), date_create('1999-01-29 09:00:00'),
				date_create('1999-01-30 09:00:00'), date_create('1999-01-31 09:00:00'),
				date_create('2000-01-01 09:00:00'),
				date_create('2000-01-02 09:00:00'), date_create('2000-01-03 09:00:00'),
				date_create('2000-01-04 09:00:00'), date_create('2000-01-05 09:00:00'),
				date_create('2000-01-06 09:00:00'), date_create('2000-01-07 09:00:00'),
				date_create('2000-01-08 09:00:00'), date_create('2000-01-09 09:00:00'),
				date_create('2000-01-10 09:00:00'), date_create('2000-01-11 09:00:00'),
				date_create('2000-01-12 09:00:00'), date_create('2000-01-13 09:00:00'),
				date_create('2000-01-14 09:00:00'), date_create('2000-01-15 09:00:00'),
				date_create('2000-01-16 09:00:00'), date_create('2000-01-17 09:00:00'),
				date_create('2000-01-18 09:00:00'), date_create('2000-01-19 09:00:00'),
				date_create('2000-01-20 09:00:00'), date_create('2000-01-21 09:00:00'),
				date_create('2000-01-22 09:00:00'), date_create('2000-01-23 09:00:00'),
				date_create('2000-01-24 09:00:00'), date_create('2000-01-25 09:00:00'),
				date_create('2000-01-26 09:00:00'), date_create('2000-01-27 09:00:00'),
				date_create('2000-01-28 09:00:00'), date_create('2000-01-29 09:00:00'),
				date_create('2000-01-30 09:00:00'), date_create('2000-01-31 09:00:00'))
			),
			// Same thing, in another way
			array(
				array('freq' => 'daily', 'bymonth' => 1, 'dtstart' => '1997-09-02 09:00:00', 'until' => '2000-01-31 09:00:00'),
				array(date_create('1998-01-01 09:00:00'),
				date_create('1998-01-02 09:00:00'), date_create('1998-01-03 09:00:00'),
				date_create('1998-01-04 09:00:00'), date_create('1998-01-05 09:00:00'),
				date_create('1998-01-06 09:00:00'), date_create('1998-01-07 09:00:00'),
				date_create('1998-01-08 09:00:00'), date_create('1998-01-09 09:00:00'),
				date_create('1998-01-10 09:00:00'), date_create('1998-01-11 09:00:00'),
				date_create('1998-01-12 09:00:00'), date_create('1998-01-13 09:00:00'),
				date_create('1998-01-14 09:00:00'), date_create('1998-01-15 09:00:00'),
				date_create('1998-01-16 09:00:00'), date_create('1998-01-17 09:00:00'),
				date_create('1998-01-18 09:00:00'), date_create('1998-01-19 09:00:00'),
				date_create('1998-01-20 09:00:00'), date_create('1998-01-21 09:00:00'),
				date_create('1998-01-22 09:00:00'), date_create('1998-01-23 09:00:00'),
				date_create('1998-01-24 09:00:00'), date_create('1998-01-25 09:00:00'),
				date_create('1998-01-26 09:00:00'), date_create('1998-01-27 09:00:00'),
				date_create('1998-01-28 09:00:00'), date_create('1998-01-29 09:00:00'),
				date_create('1998-01-30 09:00:00'), date_create('1998-01-31 09:00:00'),
				date_create('1999-01-01 09:00:00'),
				date_create('1999-01-02 09:00:00'), date_create('1999-01-03 09:00:00'),
				date_create('1999-01-04 09:00:00'), date_create('1999-01-05 09:00:00'),
				date_create('1999-01-06 09:00:00'), date_create('1999-01-07 09:00:00'),
				date_create('1999-01-08 09:00:00'), date_create('1999-01-09 09:00:00'),
				date_create('1999-01-10 09:00:00'), date_create('1999-01-11 09:00:00'),
				date_create('1999-01-12 09:00:00'), date_create('1999-01-13 09:00:00'),
				date_create('1999-01-14 09:00:00'), date_create('1999-01-15 09:00:00'),
				date_create('1999-01-16 09:00:00'), date_create('1999-01-17 09:00:00'),
				date_create('1999-01-18 09:00:00'), date_create('1999-01-19 09:00:00'),
				date_create('1999-01-20 09:00:00'), date_create('1999-01-21 09:00:00'),
				date_create('1999-01-22 09:00:00'), date_create('1999-01-23 09:00:00'),
				date_create('1999-01-24 09:00:00'), date_create('1999-01-25 09:00:00'),
				date_create('1999-01-26 09:00:00'), date_create('1999-01-27 09:00:00'),
				date_create('1999-01-28 09:00:00'), date_create('1999-01-29 09:00:00'),
				date_create('1999-01-30 09:00:00'), date_create('1999-01-31 09:00:00'),
				date_create('2000-01-01 09:00:00'),
				date_create('2000-01-02 09:00:00'), date_create('2000-01-03 09:00:00'),
				date_create('2000-01-04 09:00:00'), date_create('2000-01-05 09:00:00'),
				date_create('2000-01-06 09:00:00'), date_create('2000-01-07 09:00:00'),
				date_create('2000-01-08 09:00:00'), date_create('2000-01-09 09:00:00'),
				date_create('2000-01-10 09:00:00'), date_create('2000-01-11 09:00:00'),
				date_create('2000-01-12 09:00:00'), date_create('2000-01-13 09:00:00'),
				date_create('2000-01-14 09:00:00'), date_create('2000-01-15 09:00:00'),
				date_create('2000-01-16 09:00:00'), date_create('2000-01-17 09:00:00'),
				date_create('2000-01-18 09:00:00'), date_create('2000-01-19 09:00:00'),
				date_create('2000-01-20 09:00:00'), date_create('2000-01-21 09:00:00'),
				date_create('2000-01-22 09:00:00'), date_create('2000-01-23 09:00:00'),
				date_create('2000-01-24 09:00:00'), date_create('2000-01-25 09:00:00'),
				date_create('2000-01-26 09:00:00'), date_create('2000-01-27 09:00:00'),
				date_create('2000-01-28 09:00:00'), date_create('2000-01-29 09:00:00'),
				date_create('2000-01-30 09:00:00'), date_create('2000-01-31 09:00:00'))
			),
			// Weekly for 10 occurrences:
			array(
				array('freq' => 'weekly', 'count' => 10, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-09 09:00:00'),
				date_create('1997-09-16 09:00:00'),
				date_create('1997-09-23 09:00:00'),
				date_create('1997-09-30 09:00:00'),
				date_create('1997-10-07 09:00:00'),
				date_create('1997-10-14 09:00:00'),
				date_create('1997-10-21 09:00:00'),
				date_create('1997-10-28 09:00:00'),
				date_create('1997-11-04 09:00:00'))
			),
			// Every other week, 6 occurrences.
			array(
				array('freq' => 'weekly', 'interval' => 2, 'count' => 6, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-16 09:00:00'),
				date_create('1997-09-30 09:00:00'),
				date_create('1997-10-14 09:00:00'),
				date_create('1997-10-28 09:00:00'),
				date_create('1997-11-11 09:00:00'))
			),
			// Weekly on Tuesday and Thursday for 5 weeks, week starting on Sunday.
			array(
				array('freq' => 'weekly', 'count' => 10, 'wkst' => 'SU', 'byday' => 'TU,TH', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-04 09:00:00'),
				date_create('1997-09-09 09:00:00'),
				date_create('1997-09-11 09:00:00'),
				date_create('1997-09-16 09:00:00'),
				date_create('1997-09-18 09:00:00'),
				date_create('1997-09-23 09:00:00'),
				date_create('1997-09-25 09:00:00'),
				date_create('1997-09-30 09:00:00'),
				date_create('1997-10-02 09:00:00'))
			),
			// Every other week on Tuesday and Thursday, for 8 occurrences, week starting on Sunday
			array(
				array('freq' => 'weekly', 'interval' => 2, 'count' => 8, 'wkst' => 'SU', 'byday' => 'TU,TH', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-04 09:00:00'),
				date_create('1997-09-16 09:00:00'),
				date_create('1997-09-18 09:00:00'),
				date_create('1997-09-30 09:00:00'),
				date_create('1997-10-02 09:00:00'),
				date_create('1997-10-14 09:00:00'),
				date_create('1997-10-16 09:00:00'))
			),
			// Monthly on the 1st Friday for ten occurrences.
			array(
				array('freq' => 'monthly', 'count' => 10, 'byday' => '1FR', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-05 09:00:00'),
				date_create('1997-10-03 09:00:00'),
				date_create('1997-11-07 09:00:00'),
				date_create('1997-12-05 09:00:00'),
				date_create('1998-01-02 09:00:00'),
				date_create('1998-02-06 09:00:00'),
				date_create('1998-03-06 09:00:00'),
				date_create('1998-04-03 09:00:00'),
				date_create('1998-05-01 09:00:00'),
				date_create('1998-06-05 09:00:00'))
			),
			// Every other month on the 1st and last Sunday of the month for 10 occurrences.
			array(
				array('freq' => 'monthly', 'interval' => 2, 'count' => 10, 'byday' => '1SU,-1SU', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-07 09:00:00'),
				date_create('1997-09-28 09:00:00'),
				date_create('1997-11-02 09:00:00'),
				date_create('1997-11-30 09:00:00'),
				date_create('1998-01-04 09:00:00'),
				date_create('1998-01-25 09:00:00'),
				date_create('1998-03-01 09:00:00'),
				date_create('1998-03-29 09:00:00'),
				date_create('1998-05-03 09:00:00'),
				date_create('1998-05-31 09:00:00'))
			),
			// Monthly on the second to last Monday of the month for 6 months.
			array(
				array('freq' => 'monthly', 'count' => 6, 'byday' => '-2MO', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-22 09:00:00'),
				date_create('1997-10-20 09:00:00'),
				date_create('1997-11-17 09:00:00'),
				date_create('1997-12-22 09:00:00'),
				date_create('1998-01-19 09:00:00'),
				date_create('1998-02-16 09:00:00'))
			),
			// Monthly on the third to the last day of the month, for 6 months.
			array(
				array('freq' => 'monthly', 'count' => 6, 'bymonthday' => '-3', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-28 09:00:00'),
				date_create('1997-10-29 09:00:00'),
				date_create('1997-11-28 09:00:00'),
				date_create('1997-12-29 09:00:00'),
				date_create('1998-01-29 09:00:00'),
				date_create('1998-02-26 09:00:00'))
			),
			// Monthly on the 2nd and 15th of the month for 5 occurrences.
			array(
				array('freq' => 'monthly', 'count' => 5, 'bymonthday' => '2,15', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-15 09:00:00'),
				date_create('1997-10-02 09:00:00'),
				date_create('1997-10-15 09:00:00'),
				date_create('1997-11-02 09:00:00'))
			),
			// Monthly on the first and last day of the month for 3 occurrences.
			array(
				array('freq' => 'monthly', 'count' => 5, 'bymonthday' => '-1,1', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-30 09:00:00'),
				date_create('1997-10-01 09:00:00'),
				date_create('1997-10-31 09:00:00'),
				date_create('1997-11-01 09:00:00'),
				date_create('1997-11-30 09:00:00'))
			),
			// Every 18 months on the 10th thru 15th of the month for 10 occurrences.
			array(
				array('freq' => 'monthly', 'count' => 10, 'interval' => 18, 'bymonthday' => range(10,15), 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-10 09:00:00'),
				date_create('1997-09-11 09:00:00'),
				date_create('1997-09-12 09:00:00'),
				date_create('1997-09-13 09:00:00'),
				date_create('1997-09-14 09:00:00'),
				date_create('1997-09-15 09:00:00'),
				date_create('1999-03-10 09:00:00'),
				date_create('1999-03-11 09:00:00'),
				date_create('1999-03-12 09:00:00'),
				date_create('1999-03-13 09:00:00'))
			),
			// Every Tuesday, every other month, 6 occurences.
			array(
				array('freq' => 'monthly', 'count' => 6, 'interval' => 2, 'byday' => 'TU', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-02 09:00:00'),
				date_create('1997-09-09 09:00:00'),
				date_create('1997-09-16 09:00:00'),
				date_create('1997-09-23 09:00:00'),
				date_create('1997-09-30 09:00:00'),
				date_create('1997-11-04 09:00:00'))
			),
			// Yearly in June and July for 10 occurrences.
			array(
				array('freq' => 'yearly', 'count' => 10, 'bymonth' => '6,7', 'dtstart' => '1997-06-10 09:00:00'),
				array(date_create('1997-06-10 09:00:00'),date_create('1997-07-10 09:00:00'),
				date_create('1998-06-10 09:00:00'),date_create('1998-07-10 09:00:00'),
				date_create('1999-06-10 09:00:00'),date_create('1999-07-10 09:00:00'),
				date_create('2000-06-10 09:00:00'),date_create('2000-07-10 09:00:00'),
				date_create('2001-06-10 09:00:00'),date_create('2001-07-10 09:00:00'))
			),
			// Every 3rd year on the 1st, 100th and 200th day for 4 occurrences.
			array(
				array('freq' => 'yearly', 'count' => 4, 'interval' => '3', 'byyearday' => '1,100,200', 'dtstart' => '1997-01-01 09:00:00'),
				array(date_create('1997-01-01 09:00:00'),
				date_create('1997-04-10 09:00:00'),
				date_create('1997-07-19 09:00:00'),
				date_create('2000-01-01 09:00:00'))
			),
			// Every 20th Monday of the year, 3 occurrences.
			array(
				array('freq' => 'yearly', 'count' => 3, 'byday' => '20MO', 'dtstart' => '1997-05-19 09:00:00'),
				array(date_create('1997-05-19 09:00:00'),
				date_create('1998-05-18 09:00:00'),
				date_create('1999-05-17 09:00:00'))
			),
			// Monday of week number 20 (where the default start of the week is Monday), 3 occurrences.
			array(
				array('freq' => 'yearly', 'count' => 3, 'byweekno' => 20, 'byday' => 'MO', 'dtstart' => '1997-05-12 09:00:00'),
				array(date_create('1997-05-12 09:00:00'),
				date_create('1998-05-11 09:00:00'),
				date_create('1999-05-17 09:00:00'))
			),
			// Every Thursday in March
			array(
				array('freq' => 'yearly', 'byday' => 'TH', 'bymonth' => 3, 'dtstart' => '1997-03-13 09:00:00', 'until' => '2000-01-01'),
				array(date_create('1997-03-13 09:00:00'),
				date_create('1997-03-20 09:00:00'),
				date_create('1997-03-27 09:00:00'),
				date_create('1998-03-05 09:00:00'),
				date_create('1998-03-12 09:00:00'),
				date_create('1998-03-19 09:00:00'),
				date_create('1998-03-26 09:00:00'),
				date_create('1999-03-04 09:00:00'),
				date_create('1999-03-11 09:00:00'),
				date_create('1999-03-18 09:00:00'),
				date_create('1999-03-25 09:00:00'))
			),
			// Every Thursday, but only during June, July, and August
			array(
				array('freq' => 'yearly', 'byday' => 'TH', 'bymonth' => array(6,7,8), 'dtstart' => '1997-01-01 09:00:00', 'until' => '1999-01-01'),
				array(date_create('1997-06-05 09:00:00'),
				date_create('1997-06-12 09:00:00'),
				date_create('1997-06-19 09:00:00'),
				date_create('1997-06-26 09:00:00'),
				date_create('1997-07-03 09:00:00'),
				date_create('1997-07-10 09:00:00'),
				date_create('1997-07-17 09:00:00'),
				date_create('1997-07-24 09:00:00'),
				date_create('1997-07-31 09:00:00'),
				date_create('1997-08-07 09:00:00'),
				date_create('1997-08-14 09:00:00'),
				date_create('1997-08-21 09:00:00'),
				date_create('1997-08-28 09:00:00'),
				date_create('1998-06-04 09:00:00'),
				date_create('1998-06-11 09:00:00'),
				date_create('1998-06-18 09:00:00'),
				date_create('1998-06-25 09:00:00'),
				date_create('1998-07-02 09:00:00'),
				date_create('1998-07-09 09:00:00'),
				date_create('1998-07-16 09:00:00'),
				date_create('1998-07-23 09:00:00'),
				date_create('1998-07-30 09:00:00'),
				date_create('1998-08-06 09:00:00'),
				date_create('1998-08-13 09:00:00'),
				date_create('1998-08-20 09:00:00'),
				date_create('1998-08-27 09:00:00'))
			),
			// Every Friday the 13th, 4 occurrences.
			array(
				array('freq' => 'yearly', 'byday' => 'FR', 'bymonthday' => 13, 'count' => 4, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1998-02-13 09:00:00'),
				date_create('1998-03-13 09:00:00'),
				date_create('1998-11-13 09:00:00'),
				date_create('1999-08-13 09:00:00'))
			),
			// The first Saturday that follows the first Sunday of the month
			array(
				array('freq' => 'monthly', 'byday' => 'SA', 'bymonthday' => array(7,8,9,10,11,12,13), 'count' => 10, 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-09-13 09:00:00'),
				date_create('1997-10-11 09:00:00'),
				date_create('1997-11-08 09:00:00'),
				date_create('1997-12-13 09:00:00'),
				date_create('1998-01-10 09:00:00'),
				date_create('1998-02-07 09:00:00'),
				date_create('1998-03-07 09:00:00'),
				date_create('1998-04-11 09:00:00'),
				date_create('1998-05-09 09:00:00'),
				date_create('1998-06-13 09:00:00'))
			),
			// Every four years, the first Tuesday after a Monday in November, 3 occurrences (U.S. Presidential Election day):
			array(
				array('freq' => 'yearly', 'interval' => 4, 'bymonth' => 11, 'byday' => 'TU', 'bymonthday' => array(2,3,4,5,6,7,8), 'count' => 3, 'dtstart' => '1996-11-05 09:00:00'),
				array(date_create('1996-11-05 09:00:00'),
				date_create('2000-11-07 09:00:00'),
				date_create('2004-11-02 09:00:00'))
			),
			// The 3rd instance into the month of one of Tuesday, Wednesday or Thursday, for the next 3 months:
			array(
				array('freq' => 'monthly', 'byday' => 'TU,WE,TH', 'bysetpos' => 3, 'count' => 3, 'dtstart' => '1997-09-04 09:00:00'),
				array(date_create('1997-09-04 09:00:00'),
				date_create('1997-10-07 09:00:00'),
				date_create('1997-11-06 09:00:00'))
			),
			// The 2nd to last weekday of the month, 3 occurrences.
			array(
				array('freq' => 'monthly', 'byday' => 'MO,TU,WE,TH,FR', 'bysetpos' => -2, 'count' => 3, 'dtstart' => '1997-09-29 09:00:00'),
				array(date_create('1997-09-29 09:00:00'),
				date_create('1997-10-30 09:00:00'),
				date_create('1997-11-27 09:00:00'))
			),
			// todo HOURLY, MINUTELY, SECONDLY
		);
	}

	/**
	 * @dataProvider rfcExamples
	 */
	public function testRfcExamples($rule, $occurrences)
	{
		$rule = new RRule($rule);
		$this->assertEquals($occurrences, $rule->getOccurrences());
		foreach ( $occurrences as $date ) {
			$this->assertTrue($rule->occursAt($date), 'RRule occurs at: '.$date->format('r'));
		}
	}

	/**
	 * Just some more random rules found here and there. Some of them
	 * might not bring any additional value to the tests to be honest, but
	 * it's good to test them anyway.
	 */
	public function variousRules()
	{
		return array(
			array(
				array('freq' => 'daily', 'count' => 3, 'byday' => 'TU,TH', 'dtstart' => '2007-01-01'),
				array(date_create('2007-01-02'), date_create('2007-01-04'), date_create('2007-01-09'))
			),
			array(
				array('freq' => 'weekly', 'count' => 3, 'byday' => 'TU,TH', 'dtstart' => '2007-01-01'),
				array(date_create('2007-01-02'), date_create('2007-01-04'), date_create('2007-01-09'))
			),
			array(
				array('freq' => 'daily', 'count' => 3, 'byday' => 'TU,TH', 'dtstart' => '2007-01-01', 'bysetpos' => 1),
				array(date_create('2007-01-02'), date_create('2007-01-04'), date_create('2007-01-09'))
			),
			array(
				array('freq' => 'weekly', 'count' => 3, 'byday' => 'TU,TH', 'dtstart' => '2007-01-01', 'bysetpos' => 1),
				array(date_create('2007-01-02'), date_create('2007-01-09'), date_create('2007-01-16'))
			),
			// The week number 1 may be in the last year.
			array(
				array('freq' => 'yearly', 'count' => 3, 'byweekno' => 1, 'byday' => 'MO', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-12-29 09:00:00'),
				date_create('1999-01-04 09:00:00'),
				date_create('2000-01-03 09:00:00'))
			),
			// And the week numbers greater than 51 may be in the next year.
			array(
				array('freq' => 'yearly', 'count' => 3, 'byweekno' => 52, 'byday' => 'SU', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1997-12-28 09:00:00'),
				date_create('1998-12-27 09:00:00'),
				date_create('2000-01-02 09:00:00'))
			),
			// Only some years have week number 53
			array(
				array('freq' => 'yearly', 'count' => 3, 'byweekno' => 53, 'byday' => 'MO', 'dtstart' => '1997-09-02 09:00:00'),
				array(date_create('1998-12-28 09:00:00'),
				date_create('2004-12-27 09:00:00'),
				date_create('2009-12-28 09:00:00'))
			)
		);
	}

	/**
	 * @dataProvider variousRules
	 */
	public function testVariousRules($rule, $occurrences)
	{
		$rule = new RRule($rule);
		$this->assertEquals($occurrences, $rule->getOccurrences());
		foreach ( $occurrences as $date ) {
			$this->assertTrue($rule->occursAt($date), 'RRule occurs at: '.$date->format('r'));
		}
	}

	/**
	 * Test that occursAt doesn't return true for wrong dates
	 */
	public function notOccurrences()
	{
		return array(
			array(
				array('FREQ' => 'YEARLY', 'DTSTART' => '1999-09-02'),
				array('1999-09-01','1999-09-03')
			),
			array(
				array('FREQ' => 'YEARLY', 'DTSTART' => '1999-09-02', 'UNTIL' => '2000-09-02'),
				array('2001-09-02')
			),
			array(
				array('FREQ' => 'YEARLY', 'DTSTART' => '1999-09-02', 'COUNT' => 3),
				array('2010-09-02')
			),
			array(
				array('FREQ' => 'YEARLY', 'DTSTART' => '1999-09-02', 'INTERVAL' => 2),
				array('2000-09-02', '2002-09-02')
			),
			array(
				array('FREQ' => 'MONTHLY', 'DTSTART' => '1999-09-02', 'INTERVAL' => 2),
				array('1999-10-02', '1999-12-02')
			),
		);
	}

	/**
	 * @dataProvider notOccurrences
	 */
	public function testDoesNotOccursAt($rule, $not_occurences)
	{
		foreach ( $not_occurences as $date ) {
			$this->assertFalse((new RRule($rule))->occursAt($date), $date);
		}
	}
}