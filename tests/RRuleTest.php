<?php

use RRule\RRule;

class RRuleTest extends PHPUnit_Framework_TestCase
{
	public function invalidRules()
	{
		return array(
			array([]),
			array(['FREQ' => 'foobar']),
			array(['FREQ' => 'DAILY', 'INTERVAL' => -1]),
			array(['FREQ' => 'DAILY', 'UNTIL' => 'foobar']),
			array(['FREQ' => 'DAILY', 'COUNT' => -1]),

			// The BYDAY rule part MUST NOT be specified with a numeric value
			// when the FREQ rule part is not set to MONTHLY or YEARLY.
			array(['FREQ' => 'DAILY', 'BYDAY' => ['1MO']]),
			array(['FREQ' => 'WEEKLY', 'BYDAY' => ['1MO']]),
			// The BYDAY rule part MUST NOT be specified with a numeric value
			// with the FREQ rule part set to YEARLY when the BYWEEKNO rule part is specified.
			array(['FREQ' => 'YEARLY', 'BYDAY' => ['1MO'], 'BYWEEKNO' => 20]),

			array(['FREQ' => 'DAILY', 'BYMONTHDAY' => 0]),
			array(['FREQ' => 'DAILY', 'BYMONTHDAY' => 32]),
			array(['FREQ' => 'DAILY', 'BYMONTHDAY' => -32]),
			// The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule
			// part is set to WEEKLY.
			array(['FREQ' => 'WEEKLY', 'BYMONTHDAY' => 1]),

			array(['FREQ' => 'YEARLY', 'BYYEARDAY' => 0]),
			array(['FREQ' => 'YEARLY', 'BYYEARDAY' => 367]),
			// The BYYEARDAY rule part MUST NOT be specified when the FREQ
			// rule part is set to DAILY, WEEKLY, or MONTHLY.
			array(['FREQ' => 'DAILY', 'BYYEARDAY' => 1]),
			array(['FREQ' => 'WEEKLY', 'BYYEARDAY' => 1]),
			array(['FREQ' => 'MONTHLY', 'BYYEARDAY' => 1]),

			// BYSETPOS rule part MUST only be used in conjunction with another
			// BYxxx rule part.
			array(['FREQ' => 'DAILY', 'BYSETPOS' => -1]),
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

// date_create\(([0-9]+), ([0-9]+), ([0-9]+)[ ,0-9\)]+

	public function yearlyRules()
	{
		return array(
			array([],[date_create('1997-09-02'),date_create('1998-09-02'), date_create('1999-09-02')]),
			array(['INTERVAL' => 2], [date_create('1997-09-02'),date_create('1999-09-02'),date_create('2001-09-02')]),
			array(['DTSTART' => '2000-02-29'], [date_create('2000-02-29'),date_create('2004-02-29'),date_create('2008-02-29')]),
			array(['BYMONTH' => [1,3]], [date_create('1998-01-02'),date_create('1998-03-02'),date_create('1999-01-02')]),
			array(['BYMONTHDAY' => [1,3]], [date_create('1997-09-03'),date_create('1997-10-01'),date_create('1997-10-03')]),
			array(['BYMONTH' => [1,3], 'BYMONTHDAY' => [5,7]], [date_create('1998-01-05'),date_create('1998-01-07'),date_create('1998-03-05')]),
			array(['BYDAY' => ['TU','TH']], [date_create('1997-09-02'),date_create('1997-09-04'),date_create('1997-09-09')]),
			array(['BYDAY' => ['SU']], [date_create('1997-09-07'),date_create('1997-09-14'),date_create('1997-09-21')]),
			array(['BYDAY' => ['1TU','-1TH']], [date_create('1997-12-25'),date_create('1998-01-06'),date_create('1998-12-31')]),
			array(['BYDAY' => ['3TU','-3TH']], [date_create('1997-12-11'),date_create('1998-01-20'),date_create('1998-12-17')]),
			array(['BYMONTH' => [1,3], 'BYDAY' => ['TU','TH']], [date_create('1998-01-01'),date_create('1998-01-06'),date_create('1998-01-08')]),
			array(['BYMONTH' => [1,3], 'BYDAY' => ['1TU','-1TH']], [date_create('1998-01-06'),date_create('1998-01-29'),date_create('1998-03-03')]),
			// This is interesting because the TH(-3) ends up before the TU(3).
			array(['BYMONTH' => [1,3], 'BYDAY' => ['3TU','-3TH']], [date_create('1998-01-15'),date_create('1998-01-20'),date_create('1998-03-12')]),
			array(['BYMONTHDAY' => [1,3], 'BYDAY' => ['TU','TH']], [date_create('1998-01-01'),date_create('1998-02-03'),date_create('1998-03-03')]),
			array(['BYMONTHDAY' => [1,3], 'BYDAY' => ['TU','TH'], 'BYMONTH' => [1,3]], [date_create('1998-01-01'),date_create('1998-03-03'),date_create('2001-03-01')]),
			array(['BYYEARDAY' => [1,100,200,365], 'COUNT' => 4], [date_create('1997-12-31'),date_create('1998-01-01'),date_create('1998-04-10'), date_create('1998-07-19')]),
			array(['BYYEARDAY' => [-365, -266, -166, -1], 'COUNT' => 4], [date_create('1997-12-31'),date_create('1998-01-01'),date_create('1998-04-10'), date_create('1998-07-19')]),
			array(['BYYEARDAY' => [1,100,200,365], 'BYMONTH' => [4,7], 'COUNT' => 4], [date_create('1998-04-10'),date_create('1998-07-19'),date_create('1999-04-10'), date_create('1999-07-19')]),
			array(['BYYEARDAY' => [-365, -266, -166, -1], 'BYMONTH' => [4,7], 'COUNT' => 4], [date_create('1998-04-10'),date_create('1998-07-19'),date_create('1999-04-10'), date_create('1999-07-19')]),
			array(['BYWEEKNO' => 20],[date_create('1998-05-11'),date_create('1998-05-12'),date_create('1998-05-13')]),
			// That's a nice one. The first days of week number one may be in the last year.
			array(['BYWEEKNO' => 1, 'BYDAY' => 'MO'], [date_create('1997-12-29'), date_create('1999-01-04'), date_create('2000-01-03')]),
			// Another nice test. The last days of week number 52/53 may be in the next year.
			array(['BYWEEKNO' => 52, 'BYDAY' => 'SU'], [date_create('1997-12-28'), date_create('1998-12-27'), date_create('2000-01-02')]),
			array(['BYWEEKNO' => -1, 'BYDAY' => 'SU'], [date_create('1997-12-28'), date_create('1999-01-03'), date_create('2000-01-02')]),
			array(['BYWEEKNO' => 53, 'BYDAY' => 'MO'], [date_create('1998-12-28'), date_create('2004-12-27'), date_create('2009-12-28')]),

			// FIXME (time part missing)
			// array(['BYHOUR' => [6, 18]], [date_create('1997-09-02'),date_create('1998-09-02'),date_create('1998-09-02')]),
			// array(['BYMINUTE'=> [6, 18]], ['1997-09-02', '1997-09-02', '1998-09-02']),
			// array(['BYSECOND' => [6, 18]], ['1997-09-02', '1997-09-02', '1998-09-02']),
			// array(['BYHOUR' => [6, 18], 'BYMINUTE' => [6, 18]],  ['1997-09-02','1997-09-02','1998-09-02']),
			// array(['BYHOUR' => [6, 18], 'BYSECOND' => [6, 18]], ['1997-09-02','1997-09-02','1998-09-02']),
			// array(['BYMINUTE' => [6, 18], 'BYSECOND' => [6, 18]], ['1997-09-02','1997-09-02','1997-09-02']),
			// array(['BYHOUR'=>[6, 18],'BYMINUTE'=>[6, 18],'BYSECOND'=>[6, 18]],['1997-09-02','1997-09-02','1997-09-02']),
			// array(['BYMONTHDAY'=>15,'BYHOUR'=>[6, 18],'BYSETPOS'=>[3, -3],[date_create('1997-11-15'),date_create('1998-02-15'),date_create('1998-11-15')])
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
			array([],[date_create('1997-09-02'),date_create('1997-10-02'),date_create('1997-11-02')]),
			array(['INTERVAL'=>2],[date_create('1997-09-02'),date_create('1997-11-02'),date_create('1998-01-02')]),
			array(['INTERVAL'=>18],[date_create('1997-09-02'),date_create('1999-03-02'),date_create('2000-09-02')]),
			array(['BYMONTH' => [1, 3]],[date_create('1998-01-02'),date_create('1998-03-02'),date_create('1999-01-02')]),
			array(['BYMONTHDAY' => [1, 3]],[date_create('1997-09-03'),date_create('1997-10-01'),date_create('1997-10-03')]),
			array(['BYMONTHDAY' => [5, 7], 'BYMONTH' => [1, 3]], [date_create('1998-01-05'), date_create('1998-01-07'), date_create('1998-03-05')]),
			array(['BYDAY' => ['TU', 'TH']], [date_create('1997-09-02'),date_create('1997-09-04'),date_create('1997-09-09')]),
			// Third Monday of the month
			array(['BYDAY' => '3MO'],[date_create('1997-09-15'),date_create('1997-10-20'),date_create('1997-11-17')]),
			array(['BYDAY' => '1TU,-1TH'],[date_create('1997-09-02'),date_create('1997-09-25'),date_create('1997-10-07')]),
			array(['BYDAY' => '3TU,-3TH'],[date_create('1997-09-11'),date_create('1997-09-16'),date_create('1997-10-16')]),
			array(['BYDAY' => 'TU,TH', 'BYMONTH' => [1, 3]],[date_create('1998-01-01'),date_create('1998-01-06'),date_create('1998-01-08')]),
			array(['BYMONTH' => [1, 3], 'BYDAY' => '1TU, -1TH'],[date_create('1998-01-06'),date_create('1998-01-29'),date_create('1998-03-03')]),
			array(['BYMONTH' => [1, 3], 'BYDAY' => '3TU, -3TH'],[date_create('1998-01-15'),date_create('1998-01-20'),date_create('1998-03-12')]),
			array(['BYMONTHDAY' => [1, 3], 'BYDAY' => ['TU', 'TH']], [date_create('1998-01-01'),date_create('1998-02-03'),date_create('1998-03-03')]),
			array(['BYMONTH' => [1, 3], 'BYMONTHDAY' => [1, 3], 'BYDAY' => ['TU', 'TH']],[date_create('1998-01-01'),date_create('1998-03-03'),date_create('2001-03-01')]),

			// array(['BYHOUR'=> [6, 18],['1997-09-02',date_create('1997-10-02'),date_create('1997-10-02')]),
			// array(['BYMINUTE'=> [6, 18],['1997-09-02','1997-09-02',date_create('1997-10-02')]),
			// array(['BYSECOND' => [6, 18],['1997-09-02','1997-09-02',date_create('1997-10-02')]),
			// array(['BYHOUR'=>[6, 18],'BYMINUTE'=>[6, 18]],['1997-09-02','1997-09-02',date_create('1997-10-02')]),
			// array(['BYHOUR'=>[6, 18],'BYSECOND'=>[6, 18]],['1997-09-02','1997-09-02',date_create('1997-10-02')]),
			// array(['BYMINUTE'=>[6, 18],'BYSECOND'=>[6, 18]],['1997-09-02','1997-09-02','1997-09-02']),
			// array(['BYHOUR'=>[6, 18],'BYMINUTE'=>[6, 18],'BYSECOND'=>[6, 18]],['1997-09-02','1997-09-02','1997-09-02']),
			// array(['BYMONTHDAY'=>[13, 17],'BYHOUR'=>[6, 18],'BYSETPOS'=>[3, -3]],[date_create('1997-09-13'),date_create('1997-09-17'),date_create('1997-10-13')])
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

	public function weeklyRules()
	{
		return array(
			array([],[date_create('1997-09-02'), date_create('1997-09-09'), date_create('1997-09-16')]),
			array(['interval'=>2],[date_create('1997-09-02'),date_create('1997-09-16'),date_create('1997-09-30')]),
			array(['interval'=>20],[date_create('1997-09-02'),date_create('1998-01-20'),date_create('1998-06-09')]),
			array(['bymonth'=>[1, 3]],[date_create('1998-01-06'),date_create('1998-01-13'),date_create('1998-01-20')]),
			array(['byday'=> ['TU', 'TH']],[date_create('1997-09-02'), date_create('1997-09-04'), date_create('1997-09-09')]),

			# This test is interesting, because it crosses the year
			# boundary in a weekly period to find day '1' as a
			# valid recurrence.
			array(['bymonth'=>[1, 3],'byday'=>['TU', 'TH']],[date_create('1998-01-01'), date_create('1998-01-06'), date_create('1998-01-08')]),

			// array(['byhour'=>[6, 18]],[date_create('1997-09-02'),date_create('1997-09-09'),date_create('1997-09-09')]),
			// array(['byminute'=>[6, 18]],[date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-09')]),
			// array(['bysecond'=> [6, 18]],[date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-09')]),
			// array(['byhour'=> [6, 18],'byminute'=>[6, 18]],[date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-09')]),
			// array(['byhour'=>[6, 18],'bysecond'=>[6, 18]],[date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-09')]),
			// array(['byminute'=>[6, 18],'bysecond'=>[6, 18]],[date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-02')]),
			// array(['byhour'=>[6, 18],'byminute'=>[6, 18],'bysecond'=>[6, 18]],[date_create('1997-09-02'),date_create('1997-09-02'),date_create('1997-09-02')]),
			// array(['byday'=>['TU', 'TH'],'byhour'=>[6, 18],'bysetpos'=>[3, -3]],[date_create('1997-09-02'),date_create('1997-09-04'),date_create('1997-09-09')])
		);
	}
	/**
	 * @dataProvider weeklyRules
	 */
	public function testWeekly($rule, $occurrences)
	{
		$rule = new RRule(array_merge([
			'FREQ' => 'WEEKLY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		], $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
	}

	public function dailyRules()
	{
		return array(
			array([], [date_create('1997-09-02'),date_create('1997-09-03'),date_create('1997-09-04')]),
			array(['interval'=>2],[date_create('1997-09-02'), date_create('1997-09-04'), date_create('1997-09-06')]),
			array(['interval'=>92],[date_create('1997-09-02'), date_create('1997-12-03'), date_create('1998-03-05')]),
			array(['bymonth'=>[1, 3]],[date_create('1998-01-01'), date_create('1998-01-02'), date_create('1998-01-03')]),
			array(['bymonthday'=>[1, 3]],[date_create('1997-09-03'), date_create('1997-10-01'), date_create('1997-10-03')]),
			array(['bymonth'=>[1, 3],'bymonthday'=>[5, 7]],[date_create('1998-01-05'), date_create('1998-01-07'), date_create('1998-03-05')]),
			array(['byday'=>['TU', 'TH']],[date_create('1997-09-02'), date_create('1997-09-04'), date_create('1997-09-09')]),
			array(['bymonth'=> [1, 3], 'byday'=> ['TU', 'TH']],[date_create('1998-01-01'), date_create('1998-01-06'), date_create('1998-01-08')]),
			array(['bymonthday'=> [1, 3], 'byday'=>['TU', 'TH']],[date_create('1998-01-01'), date_create('1998-02-03'), date_create('1998-03-03')]),
			array(['bymonth'=>[1, 3],'bymonthday'=>[1, 3],'byday'=>['TU', 'TH']],[date_create('1998-01-01'), date_create('1998-03-03'), date_create('2001-03-01')]),
			// array(['count'=>4,'byyearday'=>[1, 100, 200, 365]],[date_create('1997-12-31'), date_create('1998-01-01'), date_create('1998-04-10'), date_create('1998-07-19')]),
			// array(['count'=>4,'byyearday'=>[-365, -266, -166, -1]],[date_create('1997-12-31'), date_create('1998-01-01'), date_create('1998-04-10'), date_create('1998-07-19')]),
			// array(['count'=>4, 'bymonth'=>[1, 7],'byyearday'=>[1, 100, 200, 365]],[date_create('1998-01-01'),date_create('1998-07-19'),date_create('1999-01-01'),date_create('1999-07-19')]),
			// array(['count'=>4, 'bymonth' => [1, 7], 'byyearday' => [-365, -266, -166, -1]],[date_create('1998-01-01'), date_create('1998-07-19'), date_create('1999-01-01'), date_create('1999-07-19')]),
			// array(['byweekno' => 20], [date_create('1998-05-11'), date_create('1998-05-12'), date_create('1998-05-13')]),
			// array(['byweekno' => 1, 'byday' => 'MO'],[date_create('1997-12-29'),date_create('1999-01-04'),date_create('2000-01-03')]),
			// array(['byweekno' => 52, 'byday' => 'SU'], [date_create('1997-12-28'),  date_create('1998-12-27'), date_create('2000-01-02')]),
			// array(['byweekno' => -1, 'byday' => 'SU'],[date_create('1997-12-28'),date_create('1999-01-03'),date_create('2000-01-02')]),
			// array(['byweekno'=>53,'byday'=>'MO'],[date_create('1998-12-28'), date_create('2004-12-27'), date_create('2009-12-28')])
		);
	}
	/**
	 * @dataProvider dailyRules
	 */
	public function testDaily($rule, $occurrences)
	{
		$rule = new RRule(array_merge([
			'FREQ' => 'DAILY',
			'COUNT' => 3,
			'DTSTART' => '1997-09-02'
		], $rule));
		$this->assertEquals($occurrences, $rule->getOccurrences());
	}


//     def testDailyByHour(self):
//         self.assertEqual(list(rrule(DAILY,

//      byhour=(6, 18),

// [date_create('1997-09-02'),
//  date_create('1997-09-03'),
//  date_create('1997-09-03')])

//     def testDailyByMinute(self):
//         self.assertEqual(list(rrule(DAILY,

//      byminute=(6, 18),

// [date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-03')])

//     def testDailyBySecond(self):
//         self.assertEqual(list(rrule(DAILY,

//      bysecond=(6, 18),

// [date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-03')])

//     def testDailyByHourAndMinute(self):
//         self.assertEqual(list(rrule(DAILY,

//      byhour=(6, 18),
//      byminute=(6, 18),

// [date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-03')])

//     def testDailyByHourAndSecond(self):
//         self.assertEqual(list(rrule(DAILY,

//      byhour=(6, 18),
//      bysecond=(6, 18),

// [date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-03')])

//     def testDailyByMinuteAndSecond(self):
//         self.assertEqual(list(rrule(DAILY,

//      byminute=(6, 18),
//      bysecond=(6, 18),

// [date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-02')])

//     def testDailyByHourAndMinuteAndSecond(self):
//         self.assertEqual(list(rrule(DAILY,

//      byhour=(6, 18),
//      byminute=(6, 18),
//      bysecond=(6, 18),

// [date_create('1997-09-02'),
//  date_create('1997-09-02'),
//  date_create('1997-09-02')])

//     def testDailyBySetPos(self):
//         self.assertEqual(list(rrule(DAILY,

//      byhour=(6, 18),
//      byminute=(15, 45),
//      bysetpos=(3, -3),

// [date_create('1997-09-02'),
//  date_create('1997-09-03'),
//  date_create('1997-09-03')])
}