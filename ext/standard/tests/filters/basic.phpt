--TEST--
basic stream filter tests
--FILE--
<?php
# vim600:syn=php:

$text = "Hello There!";
$filters = array("string.rot13", "string.toupper", "string.tolower");

function filter_test($names)
{
	$fp = tmpfile();
	fwrite($fp, $GLOBALS["text"]);
	rewind($fp);
	foreach ($names as $name) {
		echo "filter: $name\n";
		var_dump(stream_filter_prepend($fp, $name));
	}
	var_dump(fgets($fp));
	fclose($fp);
}

foreach ($filters as $filter) {
	filter_test(array($filter));
}

filter_test(array($filters[0], $filters[1]));

?>
--EXPECT--
filter: string.rot13
resource(5) of type (stream filter)
string(12) "Uryyb Gurer!"
filter: string.toupper
resource(7) of type (stream filter)
string(12) "HELLO THERE!"
filter: string.tolower
resource(9) of type (stream filter)
string(12) "hello there!"
filter: string.rot13
resource(11) of type (stream filter)
filter: string.toupper
resource(12) of type (stream filter)
string(12) "URYYB GURER!"