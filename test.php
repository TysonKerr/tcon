<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>TCON Test</title>
</head>
<body>

<?php

set_time_limit(1);

require __DIR__ . '/tcon.php';
use function tcon\tcon_parse;

function test_tcon($name, $input, $expected) {
    $result = tcon\Tcon::parse($input, false);
    $pass = $result === $expected;
    $display = $pass ? 'pass' : 'fail';
    
    echo "<div>$name: $display</div>";
    
    if (!$pass) {
        echo '<pre>';
        var_dump(['tested' => $input, 'expected' => $expected, 'result' => $result]);
        echo '</pre>';
    }
}

require __DIR__ . '/tests/basic.php';
require __DIR__ . '/tests/files.php';

?>

</body>
</html>
