<?php

/**
 * Licensed under the MIT license.
 *
 * For the full copyright and license information, please view the LICENSE file.
 *
 * @author Rémi Lanvin <remi@cloudconnected.fr>
 * @link https://github.com/rlanvin/php-rrule
 */

namespace RRule;

interface RRuleInterface extends \Iterator, \ArrayAccess, \Countable
{
	public function getOccurrences();

	/**
	 * @param date|null $begin
	 * @param date|null $end
	 * @return array Returns an array of DateTime
	 */
	public function getOccurrencesBetween($begin, $end);

	public function occursAt($date);

	public function isFinite();

	public function isInfinite();
}