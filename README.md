Interp
======
Interp is a PHP data interpolation package that can interpolate data using difference interpolation as well as the Lagrange interpolation formula.


Installation
------------
`Interp` can be installed with `composer` like so:
```
$ composer require marando/interp
```


Usage
-----

### Difference Interpolation with `Interp3` and `Interp5`

Both the `Interp3` and `Interp5` objects can interpolate data using the second and third difference interpolation methods respectively. This method requires that all data points are equidistant.

#### Initialization 

An `Interp3` or `Interp5` object can be created by either calling the default constructor or the `init()` static constructor. You must supply the first and last x-values as well as the full table of y-values:

```php
$i3 = new Interp3(1, 7, [1, 2, 3, 4, 5, 6, 7]);
$i3 = Interp3::init(1, 7, [1, 2, 3, 4, 5, 6, 7]);
```

#### Interpolation by `x` Value
Call upon the `x()` method to interpolate an x value second argument returns by reference the interpolated value of y:

```php
$i3 = new Interp3(1, 7, [1, 2, 3, 4, 5, 6, 7]);

$i3->x(1.0, $y);  // Result: y = 1
$i3->x(2.3, $y);  // Result: y = 2.3
```

The value you provide must be within the range of the initialized data or an `OutOfRangeException` will occur:
```php
$i3 = new Interp3(1, 7, [1, 2, 3, 4, 5, 6, 7]);

$i3->x(100, $y);  
```
```
OutOfRangeException: The x value '12.3' is out of the range [1, 7].
```

The third argument returns the last difference from the table of differences:
```php
$i3 = new Interp3(1, 7, [1.234, 2.45, 3.345, 4.231, 5.56, 6.233, 7.653]);

$i3->x(2.3, $y, $c);
print $c;
```
Output:
```
0.321
```

#### Interpolation by `n` Interpolation Factor
You can also interpolate data by providing an interplation factor as seen here:
```php
$i3 = new Interp3(1, 3, [1, 2, 3]);
$i3->n(0, $x, $y);

print $x;
print $y;
```
Output:
```
2
2
```

If the initialized data has more than three values, you must also specify an x-value to focus around, otherwise an `IncorrectCountException` will be thrown:
```php
$i3 = new Interp3(1, 6, [1, 2, 3, 4, 5, 6]);
$i3->n(0.5, $x, $y, $c, 5);

print $x;
print $y;
```
Output:
```
5.5
5.5
```

Also, as seen before, the last difference from the table of differences is returned, but as the fourth value here.


#### Interpolation of Extrema
The extremum of the data can be found by calling the `extremum()` function. Both the `x` and `y` value at the extremum are returned by reference as well as the last difference from the table of differences:
```php
$i3 = new Interp3(1, 3, [0.71, 0.68, 0.79]);
$i3->extremum($x, $y, $c);

print $x;
print $y;
print $c;
```
Output:
```
1.7142857142857
0.67428571428571
0.1
```

If no extremum can be found a `NoExtremumException` will be thrown.

#### Interpolation of the Zero

Interpolation at `y = 0` can be found by using the `zero()` function which works exactly the same as the `extremum()` function. If no zero can be found a `NoZeroException` will be thrown.

```php
$i3 = new Interp3(1, 6, [-2, -1, 0, 1, 2, 3]);
$i3->zero($x, $y, $c);

print $x;
print $y;
print $c;
```
Output:
```
3
0
0
```

#### Strictness

The `Interp3` and `Interp5` objects have a property called `strict` which if set to true restricts all interpolation factors to safe values between -0.5 and 0.5, otherwise the limit is set to [-1, 1]


### Lagrange Interpolation

Lagrange interpolation can be performed with the `Lagrange` object. You must provide the object a table of x and y values by using either the default constructor or the `init()` method:

```php
// Initialize Lagrange object with data
$l = Lagrange::init($data = [
  [29.43, .4913598528],
  [30.97, .5145891926],
  [27.69, .4646875083],
  [28.11, .4711658342],
  [31.58, .5236885653],
  [33.05, .5453707057],
]);
```
Then use the `x()` method to interpolate a given x-value. The value of y is returned by reference:
```php
$l->x(30, $y);
print $y
```
Output:
```
0.5
```






