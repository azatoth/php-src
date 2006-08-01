--TEST--
date OO interface
--FILE--
<?php
class _d extends DateTime {
	function __construct() {
	}
}
class _t extends DateTimeZone {
	function __construct() {
	}
}

$d = new DateTime;
var_dump($d->format("Y-m-d H:i:s"));

$d = new _d;
var_dump($d->format("Y-m-d H:i:s"));


$t = new DateTimeZone("UTC");
var_dump($t->getName());

$t = new _t;
var_dump($t->getName());

try {
	new DateTimeZone("GottaFindThisOne");
} catch (Exception $e) {
	echo $e->getMessage(),"\n";
}

echo "DONE\n";
?>
--EXPECTF--
string(19) "%d-%d-%d %d:%d:%d"

Warning: DateTime::format(): The DateTime object has not been correctly initialized by its constructor in %soo_001.php on line %d
bool(false)
string(3) "UTC"

Warning: DateTimeZone::getName(): The DateTimeZone object has not been correctly initialized by its constructor in %soo_001.php on line %d
bool(false)
DateTimeZone::__construct(): Unknown or bad timezone (GottaFindThisOne)
DONE
