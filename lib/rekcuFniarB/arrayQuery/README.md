arrayQuery
==========

A simple PHP library implementing querying multidimensional arrays.

Examples:
---------

```php
$arr = array(
    'foo' => array(
        'bar' => array(
            'baz' => 'bazzz',
            'baz2' => 'bazzz2'
        ),
        'bar2' => 'barrrr2'
    ),
    'foo2' => 'foooo'
);

$arrobj = \rekcuFniarB\arrayQuery\arrayObject($arr);

echo $arrobj->get('foo.bar.baz'); // "bazzz"
$arrobj->set('foo.bar.q', 'qwerty');
$arrobj->del('foo.bar2');
```

In case of you want to access existing array without making a copy of the data (passing array by reference):

```php
$aq = \rekcuFniarB\arrayQuery\arrayQuery();
$aq($arr)->set('foo.bar.baz', 'qwerty');
echo $aq($arr)->get('foo.bar.baz');
```
