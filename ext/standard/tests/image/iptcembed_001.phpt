--TEST--
iptcembed() and wrong file
--FILE--
<?php

$file = dirname(__FILE__).'/iptcembed_001.data';
$fp = fopen($file, "w");
fwrite($fp, b"-1-1");
fclose($fp);

var_dump(iptcembed(-1, $file, -1));

echo "Done\n";
?>
--EXPECTF--
bool(false)
Done	
