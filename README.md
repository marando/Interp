Interp
======
Interp is a PHP data interpolation package that allows data to be interpolated using the second and third difference interpolation methods as well as Lagrange interpolation method.


Installation
------------
`AstroDate` can be installed with `composer` like so:
```
$ composer require marando/astrodate
```


Usage
-----

###Interp3

`Interp3` can interpolate data using the second difference interpolation method. This method requires that all data points are equidistant.

#### Initialization 

An `Interp3` object can be created by either calling the default constructor or the `init()` static constructor. You must supply the first and last x-values as well as the full table of y-values:

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

Also, as seen before, the third argument `$c` returns by reference the last difference from the table of differences.











