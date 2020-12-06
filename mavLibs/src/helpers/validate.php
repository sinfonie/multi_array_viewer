<?php

/**
 * Simple validation functions.
 */

namespace mavLibs\src\helpers;

class validate
{
  /** checks if the parameter is a date
   * @param  string  [$date]
   * @return bool
  */

  public static function checkDate(string $date): bool
  {
    return (bool)strtotime($date);
  }

 /** checks differences between two dates
   * @param  string [$date_1] - format: YYYY-MM-DD
   * @param  string [$date_2] - format: YYYY-MM-DD
   * @param  string [$resultFormat] default '%a'
   * @return string

    * Possible result formats
    * '%y Year %m Month %d Day %h Hours %i Minute %s Seconds' =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
    * '%y Year %m Month %d Day'                               =>  1 Year 3 Month 14 Days
    * '%m Month %d Day'                                       =>  3 Month 14 Day
    * '%d Day %h Hours'                                       =>  14 Day 11 Hours
    * '%d Day'                                                =>  14 Days
    * '%h Hours %i Minute %s Seconds'                         =>  11 Hours 49 Minute 36 Seconds
    * '%i Minute %s Seconds'                                  =>  49 Minute 36 Seconds
    * '%h Hours                                               =>  11 Hours
    * '%a Days                                                =>  468 Days
  */

  public static function dateDifference(string $date_1, string $date_2, string $resultFormat = '%a')
  {
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
    $interval = date_diff($datetime1, $datetime2);
    return $interval->format($resultFormat);
  }
}
