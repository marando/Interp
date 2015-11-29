<?php

use \Marando\Interp\Interp3;
use \Marando\Interp\Interp5;
use \Marando\Interp\Lagrange;

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

class GenericTest extends PHPUnit_Framework_TestCase {

  public function test() {

    $data = [
        [1, 1],
        [3, 2],
        [7, 3],
        [3.4, 10],
    ];

    Lagrange::init($data)->x(3.1, $y);
echo $y;

    return;
    //$i3 = Interp3::init(12, 20, [1.3814294, 2.3812213, 3.3812453]);
    $interp = Interp3::init(25, 29, [-2000, -1693.4, 406.3, 2303.2, 3000]);
    //var_dump($i3);

    $interp->zero($x, $y);
    echo "\n" . $x;
    echo "\n" . $y;


    return;

    //$interp->n(.032, $x, $y, 20);
    echo "\n" . $x;
    echo "\n" . $y;


    exit;
    if ($interp->extremum($x, $y)) {
      echo "\n" . $x;
      echo "\n" . $y;
    }
    else {
      echo "no extremum";
    }

    //echo "\n" . $x;
  }

}
