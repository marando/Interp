<?php

/*
 * Copyright (C) 2015 Ashley Marando
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Marando\Interp;

use \InvalidArgumentException;
use \Marando\Interp\Exceptions\IncorrectCountException;
use \Marando\Interp\Exceptions\NoRangeException;
use \Marando\Interp\Exceptions\OutOfRangeException;

/**
 * Interpolates data using second difference interpolation.
 *
 * @property bool $strict Sets if interpolation for |n| > 0.5 is valid,
 *                        otherwise the limit is set to the range [-1, 1]
 */
class Interp3 {
  //----------------------------------------------------------------------------
  // Constants
  //----------------------------------------------------------------------------

  /**
   * The default 'strictness' for n-factor
   */
  const STRICT_DEFAULT = false;

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new Interp3 instance
   *
   * @param  float $x1 First x value
   * @param  float $xN Last x value
   * @param  array $y  Table of y values
   *
   * @throws InvalidArgumentException Occurs if an argument is the wrong type
   * @throws IncorrectCountException  Occurs if there are less than 3 y values
   * @throws NoRangeException         Occurs if the x values are the same
   */
  public function __construct($x1, $xN, array $y) {
    // Check values are all valid
    $this->checkInitValues($x1, $xN, $y, $e);
    if ($e)
      throw $e;

    // Set values as they are
    if ($xN > $x1) {
      $this->x1 = $x1;
      $this->xN = $xN;
      $this->y  = $y;
    }

    // Reverse sort and set
    else {
      $this->x1 = $xN;
      $this->xN = $x1;
      $this->y  = array_reverse($y);
    }
  }

  // // // Static

  /**
   * Creates a new Interp3 instance
   *
   * @param  float $x1 First x value
   * @param  float $xN Last x value
   * @param  array $y  Table of y values
   * @return static
   */
  public static function init($x1, $xN, array $y) {
    return new static($x1, $xN, $y);
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * First x value
   * @var float
   */
  protected $x1;

  /**
   * Last x value
   * @var float
   */
  protected $xN;

  /**
   * Array of y values
   * @var float[]
   */
  protected $y;

  /**
   * If strict mode is enabled, which disallows an n-factor > |0.5|
   * @var bool
   */
  protected $strict;

  public function __get($name) {
    switch ($name) {
      case 'strict':
        return $this->{$name};
    }
  }

  public function __set($name, $value) {
    switch ($name) {
      case 'strict':
        $this->setStrict($value);
    }
  }

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  /**
   * Interpolates an interpolation n-factor
   *
   * @param float $n  Interpolation n-factor
   * @param float $x  (returned) Interpolated value of x at n
   * @param float $y  (returned) Interpolated value of y at n
   * @param float $xt Value of x to target, (required if more than 3 y-values)
   */
  public function n($n, &$x, &$y, $xt = null) {
    if ($xt) {
      // Target the x value then interpolate
      $this->slice($xt, $x1, $x3, $yt);
      $this->interpN($n, $x1, $x3, $yt, $x, $y);
    }
    else {
      // Interpolate
      $this->interpN($n, $this->x1, $this->xN, $this->y, $x, $y);
    }

    return;
  }

  /**
   * Interpolates an x-value
   *
   * @param float $x Value of x to interpolate
   * @param float $y (returned) Interpolated value of y at x
   *
   * @throws OutOfRangeException Occurs if the x-value is out of range
   */
  public function x($x, &$y) {
    // Check if x value is out of range
    if ($x < $this->x1 || $x > $this->xN)
      throw new OutOfRangeException("The x value is out of range.");

    // Grab slice centered around gien x
    $this->slice($x, $x1, $x3, $yt);

    // Calculate the x sum and difference, and the (interpolation n-factor)
    $xΣ = $x3 + $x1;
    $xΔ = $x3 - $x1;
    $n  = (2 * $x - $xΣ) / $xΔ;

    // For calculated slice, interpolate n
    $this->interpN($n, $x1, $x3, $yt, $x, $y);
    return;
  }

  /**
   * Finds the values of x and y at the extremum
   *
   * @param  float $x (returned) Interpolated value of x at the extremum
   * @param  float $y (returned) Interpolated value of y at the extremum
   * @return bool     True if extremum found, false if it was not
   */
  public function extremum(&$x, &$y) {
    $found = false;

    // Initialize x and y
    $x;
    $y = PHP_INT_MAX;

    // Find extremum for each slice
    foreach ($this->slices() as $slice) {
      // Grab parameters of the slice
      $x1 = $slice[0];
      $x3 = $slice[1];
      $yt = $slice[2];

      // Calculate table of differences
      static::diffs($yt, $a, $b, $c);

      // Would cause division by 0, so no extremum if 0.
      if ($c == 0)
        continue;

      // Calculate n-factor, and continue if it is out of range
      $n = ($a + $b) / (-2 * $c);
      if (!$this->checkN($n))
        continue;

      // Interpolate x and y values at extremum
      $y´ = $yt[1] - pow($a + $b, 2) / (8 * $c);

      // Check if this iteration's y is < the previous
      if ($y´ < $y) {
        // New extremum found, populate variablesF
        $found = true;
        $intvl = abs($x3 - $x1) / (count($yt) - 1);
        $x     = ($x1 + $intvl) + ($n * $intvl);
        $y     = $y´;
      }
    }

    // Return true if extremum found, false if not
    return $found;
  }

  /**
   * Finds the values of x when y is zero
   *
   * @param  float $x (returned) Interpolated value of x at the zero
   * @param  float $y (returned) Interpolated value of y at the zero
   * @return bool     True if zero found, false if it was not
   */
  public function zero(&$x, &$y) {
    // Flag for zero n-factor found
    $f0 = false;

    // Initialize the interpolation parameters
    $n  = PHP_INT_MAX;
    $x1 = PHP_INT_MAX;
    $x3 = PHP_INT_MAX;
    $yt = [];

    // Iterate through the two methods for finding n
    for ($i = 0; $i < 2; $i++) {

      // Find zero for each slice
      foreach ($this->slices() as $slice) {
        // Grab parameters of the slice
        $x1´ = $slice[0];
        $x3´ = $slice[1];
        $yt´ = $slice[2];

        // Find n-factor based on current algorithm, start with better
        $n´ = $i == 0 ? static::nZeroB($yt´) : static::nZeroF($yt´);

        // Continue if invalid n-factor
        if (!$this->checkN($n´))
          continue;

        // Check if lower n-factor has been found
        if ($n´ < $n) {
          // Set the parameters
          $n  = $n´;
          $x1 = $x1´;
          $x3 = $x3´;
          $yt = $yt´;

          // Flag n-factor as found
          $f0 = true;
        }
      }
    }

    // If an n-factor for the zero has been found, interpolate it
    if ($f0)
      $this->interpN($n, $x1, $x3, $yt, $x, $y);

    // Return true if zero found, false if not
    return $f0 ? true : false;
  }

  // // // Protected

  /**
   * Checks initialization data values for validity
   *
   * @param  mixed     $x1 First x-value
   * @param  mixed     $xN Last x-value
   * @param  mixed     $y  Array of y values
   * @param  Exception $e  First exception encountered
   * @return bool          True if valid, false if errors
   */
  protected function checkInitValues($x1, $xN, $y, &$e) {
    // Break on first exception
    while ($e == null) {

      // Check that all values of y are numeric
      foreach ($y as $i)
        if (!is_numeric($i))
          $e = new InvalidArgumentException("All values of y must be numeric");

      // Check that x1 is numeric
      if (!is_numeric($x1))
        $e = new InvalidArgumentException("x1 must be numeric");

      // Check that xN is numeric
      if (!is_numeric($xN))
        $e = new InvalidArgumentException("xN must be numeric");

      // Check that there are at least 3 y values
      if (count($y) < 3)
        $e = new IncorrectCountException("Must have at least three y values");

      // Check that there is range between x1 and xN
      if ($x1 == $xN)
        $e = new NoRangeException("No range between x values");

      break;  // Force break
    }

    return $e ? false : true;
  }

  /**
   * Sets if this instance should allow for an interpolation for |n| > 0.5,
   * otherwise the maximum absolute value of n is set to 1
   *
   * @param bool $value
   */
  protected function setStrict($value) {
    $this->strict = (bool)$value;
  }

  /**
   * Divides the entire data set of this instance into successive slices of
   * lengths of three.
   *
   * For example:
   *
   *    x | y
   *   ---|--- 1
   *    0 | 2  | 2
   *    1 | 3  | | 3
   *    2 | 4  | | | 4
   *    3 | 5    | | |
   *    4 | 6      | |
   *    5 | 7        |
   *
   *
   * @return array An array in the format [x1, x3, y]
   */
  protected function slices() {
    // Number of tabular values and tabular interval
    $tabv  = 3;
    $intvl = abs($this->xN - $this->x1) / (count($this->y) - 1);

    // Number of slices
    $count = count($this->y) - $tabv + 1;

    // Generate slices
    $slices = [];
    for ($i = 0; $i < $count; $i++) {
      $x1 = $this->x1 + $intvl * $i;
      $x3 = $this->x1 + $intvl * ($i + $tabv - 1);
      $y  = array_slice($this->y, $i, 3);

      // Add the slices
      $slices[] = [$x1, $x3, $y];
    }

    return $slices;
  }

  /**
   * Checks that a value of n is valid for this instance
   *
   * @param  type                $n Value of n to check
   * @param  OutOfRangeException $e (returned) Related exception
   * @return bool                   True if value is valid, false if it is not
   */
  protected function checkN($n, Exception &$e = null) {
    if ($n == null)
      return false;

    // Basically allow |n| <= 0.5 for stricth and |n| <= 1 for not strict
    if (abs($n) > 1 || (abs($n) > 0.5 && $this->strict)) {
      $r = $this->strict ? '[-0.5, 0.5]' : '[-1, 1]';
      $e = new OutOfRangeException(
              "The n value '{$n}' is out of the range {$r}");
    }

    return $e ? false : true;
  }

  /**
   * Slices the data of this instance to a subset around the provided x-value
   *
   * @param  float $x  Value of x to target
   * @param  float $x1 (returned) First x-value
   * @param  float $x3 (returned) Last x-value
   * @param  array $y  Array of y values
   *
   * @throws OutOfRangeException Occurs if the x-value is out of range
   */
  protected function slice($x, &$x1, &$x3, array &$y = null) {
    // Number of tabular values
    $tabv = 3;

    // Check if already have appropriate number of y values
    if (count($this->y) == $tabv) {
      $x1 = 0 + $this->x1;
      $x3 = 0 + $this->xN;
      $y  = $this->y;

      // Return above values
      return;
    }

    // Check for out of range x value
    if ($x < $this->x1 || $x > $this->xN)
      throw new OutOfRangeException();

    // Find x interval
    $intvl = abs($this->xN - $this->x1) / (count($this->y) - 1);

    // Find closest value to provided x
    $cxi = 0;
    $Δx  = PHP_INT_MAX;
    for ($i = 0; $i < count($this->y); $i++) {
      $xi = $this->x1 + $intvl * $i;
      $Δ  = abs($x - $xi);
      if ($Δ < $Δx) {
        $Δx  = $Δ;
        $cxi = $i;
      }
    }

    // Number of values per tablar half, e.g.  (1) (2) 3 (4) (5) == 2
    $tabh = ($tabv - 1) / 2;

    // Fix out of range values
    if ($cxi - $tabh < 0)
      $cxi = $tabh;
    if ($cxi + $tabh > count($this->y))
      $cxi = count($this->y);

    // Slice data
    $x1 = ($this->x1 + $cxi * $intvl) - ($intvl * $tabh);
    $x3 = ($this->x1 + $cxi * $intvl) + ($intvl * $tabh);
    $y  = array_slice($this->y, $cxi - $tabh, $tabv);

    return;
  }

  /**
   * Interpolates for an interpolation n-factor
   *
   * @param float $n  Interpolation n-factor
   * @param float $x1 First x-value
   * @param float $x3 Last x-value
   * @param array $yt Array of y-values
   * @param float $x  (returned) Interpolated value of x at n
   * @param float $y  (returned) Interpolated value of y at n
   *
   * @throws IncorrectCountException Occurs if more than 3 y-values
   * @throws OutOfRangeException     Occurs if the n-favtor is out of range
   */
  protected function interpN($n, $x1, $x3, array $yt, &$x, &$y) {
    if (count($yt) != 3)
      throw new IncorrectCountException("Interpolation by n-factor is not "
      . "valid with not exactly three y values");

    // Check the n-factor and if neccesary throw range exception
    $this->checkN($n, $e);
    if ($e)
      throw $e;

    // tabular interval
    $intvl = abs($x3 - $x1) / (count($yt) - 1);

    // Get table of diffs
    static::diffs($yt, $a, $b, $c);

    // Interpolate y and find x.
    $y = $yt[1] + $n / 2 * ( ($a + $b) + $n * $c );
    $x = ($x1 + $intvl) + ($n * $intvl);

    return;
  }

  // // // Static

  /**
   * Calculates the table of differences for an array of y-values
   *
   * @param array $y Array of y-values
   * @param float $a (returned) a
   * @param float $b (returned) b
   * @param float $c (returned) c
   */
  protected static function diffs(array $y, &$a, &$b, &$c) {
    /*
     * Table of differences, three tabular values
     *
     *  y0
     *      a
     *  y1     c
     *      b
     *  y2
     *
     */

    // Calculate table of differences.
    $a = $y[1] - $y[0];
    $b = $y[2] - $y[1];
    $c = $b - $a;

    return;
  }

  protected static function better0n(array $y) {

  }

  protected static function nZeroB(array $y) {
    static::diffs($y, $a, $b, $c);

    // Find n factor by iteration.
    $n = 0;
    for ($i = 0; $i < 512; $i++) {
      $n0 = $n;
      $n  = -1 * (2 * $y[1] + $n0 * ($a + $b + $c * $n0)) /
              ($a + $b + 2 * $c * $n0);


      if ($n == $n0)
        break;  // n value was found, break loop.

      if ($i == 511)
        return null;
    }

    return $n;
  }

  protected static function nZeroF(array $y) {
    static::diffs($y, $a, $b, $c);

    // Find n factor by iteration.
    $n = 0;
    for ($i = 0; $i < 512; $i++) {
      $n0 = $n;
      $n  = (-2 * $y[1]) / ($a + $b + $c * $n0);

      if ($n == $n0)
        break;  // n value was found, break loop.

      if ($i == 511)
        return null;
    }

    return $n;
  }

}
