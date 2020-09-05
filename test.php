<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>TCON Test</title>
    <style>
        .pass {
            background-color: #bfb;
        }
        .fail {
            background-color: #fbb;
        }
        
        h4 { margin: 10px 0 5px; }
        pre { margin: 0; }
    </style>
</head>
<body>

<?php

// note: this does not check element order
function get_value_differences($arr1, $arr2) {
    if (!is_array($arr1)) return $arr2;
    if (!is_array($arr2)) return $arr1;
    $diffs = [];
    
    foreach ($arr1 as $key => $val) {
        if (!isset($arr2[$key])) {
            $diffs[$key] = $val;
            continue;
        }
        
        if (is_array($val)) {
            $val_diffs = get_value_differences($val, $arr2[$key]);
            
            if (count($val_diffs) > 0) {
                $diffs[$key] = $val_diffs;
            }
        } else if ($val !== $arr2[$key]) {
            $diffs[$key] = $val;
        }
    }
    
    foreach ($arr2 as $key => $val) {
        if (!isset($arr1[$key])) $diffs[$key] = $val;
    }
    
    return $diffs;
}

function htmlspecialchars_recursive($inp) {
    if (is_string($inp)) {
        return htmlspecialchars($inp);
    } else if (is_array($inp)) {
        $out = [];
        
        foreach ($inp as $key => $val) {
            $out[htmlspecialchars($key)] = htmlspecialchars_recursive($val);
        }
        
        return $out;
    } else {
        return $inp;
    }
}

set_time_limit(1);

require __DIR__ . '/tcon.php';
use function tcon\tcon_parse;

function test_tcon($name, $input, $expected) {
    set_time_limit(1);
    $result = tcon_parse($input, false);
    $pass = $result === $expected;
    $display = $pass ? 'pass' : 'fail';
    
    echo "<div class='$display'>$name: $display</div>";
    
    if (!$pass) {
        echo '<h4>input</h4><pre>';
        var_dump(htmlspecialchars_recursive($input));
        echo '</pre><h4>expected</h4><pre>';
        var_dump(htmlspecialchars_recursive($expected));
        echo '</pre><h4>result</h4><pre>';
        var_dump(htmlspecialchars_recursive($result));
        echo '</pre><h4>array diffs</h4><pre>';
        var_dump(htmlspecialchars_recursive(get_value_differences($result, $expected)));
        echo '</pre>';
    }
}

require __DIR__ . '/tests/basic.php';
require __DIR__ . '/tests/files.php';

?>

</body>
</html>
