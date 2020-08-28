<?php

$dir = __DIR__ . '/files';

foreach (scandir($dir) as $entry) {
    if (substr($entry, -5) === '.tcon') {
        $input = file_get_contents("$dir/$entry");
        $basename = substr($entry, 0, -5);
        $json_file = "$basename.json";
        $json = file_get_contents("$dir/$json_file");
        $expected = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        test_tcon("file $basename, tcon", $input, $expected);
        test_tcon("file $basename, json", $json, $expected);
    }
}
