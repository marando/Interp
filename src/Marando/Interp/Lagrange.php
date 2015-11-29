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

use \Marando\Interp\Exceptions\OutOfRangeException;

/**
 * Interpolates data using the Lagrange method
 */
class Lagrange {
  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  /**
   * Creates a new Lagrange interpolator from an array of (x, y) values
   * @param array $data
   */
  public function __construct(array $data) {
    $this->data = $data;
  }

  // // // Static

  /**
   * Creates a new Lagrange interpolator from an array of (x, y) values
   * @param  array  $data
   * @return static
   */
  public static function init(array $data) {
    return new static($data);
  }

  //----------------------------------------------------------------------------
  // Properites
  //----------------------------------------------------------------------------

  /**
   * (x, y) data values
   * @var array
   */
  protected $data;

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------

  /**
   * Interpolates the value of y at a given x value
   *
   * @param  type $x Value of x to interpolate
   * @param  type $y (returned) Interpolated value of y
   * @return bool    Returns false if value is out of range
   */
  public function x($x, &$y) {
    // Check if x value is within data set, if not return false
    //if (!$this->checkRange($x))
      //return false;

    $sum = 0;
    for ($i = 0; $i < count($this->data); $i++) {
      $xi   = $this->data[$i][0];
      $prod = 1;

      for ($j = 0; $j < count($this->data); $j++) {
        if ($i != $j) {
          $xj = $this->data[$j][0];
          $prod *= ($x - $xj) / ($xi - $xj);
        }
      }

      $sum += $this->data[$i][1] * $prod;
    }

    $y = $sum;
    return true;
  }

  // // // Protected

  /**
   * Checks if a given x-value is within the data range of this instance
   *
   * @param  float $x
   * @param  OutOfRangeException $e Exception if the x-value is out of range
   * @return bool                   True if within range, false if out of range
   */
  protected function checkRange($x, OutOfRangeException &$e = null) {
    // Get min and max x values for instance
    $min = min($this->data)[0];
    $max = max($this->data)[0];

    // Check range
    if ($x < $min || $x > $max)
      $e = new OutOfRangeException("The x value '{$x}' is out of the range "
              . "[{$min}, {$max}].");

    return isset($e) ? false : true;
  }

}
