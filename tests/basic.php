<?php

$tests = [
    ['', null],
    [' ', null],
    [' ,,', null],
    [' apple ', 'apple'],
    [' apple,banana ', ['apple', 'banana']],
    [' "abc def \'ghi\' " ', "abc def 'ghi' "],
    [" 'abc def \"ghi\" ' ", 'abc def "ghi" '],
    [' "abc" "def" ', ['abc', 'def']],
    [' "abc""def" ', ['abc', 'def']],
    [' apple banana ', ['apple', 'banana']],
    [' ant"bat tab"cat\'dog god\'eel ', ['ant', 'bat tab', 'cat', 'dog god', 'eel']],
    [' abc : def ', ['abc' => 'def']],
    ['abc:def', ['abc' => 'def']],
    [' abc : def : ghi', ['abc' => ['def' => 'ghi']]],
    [' abc : def : ghi jkl:mno:pqr', ['abc' => ['def' => 'ghi'], 'jkl' => ['mno' => 'pqr']]],
    [' abc:[def]', ['abc' => ['def']]],
    ['abc: [def:ghi jkl:mno] pqr stu', ['abc' => ['def' => 'ghi', 'jkl' => 'mno'], 'pqr', 'stu']],
    ['[abc][def]', [['abc'], ['def']]],
    ['a:False:False', ['a' => ['False' => false]]],
    ['false true', [false, true]],
    ['false true null', [false, true, null]],
    ['false null true', [false, null, true]],
    ['a:b:c:cat a:b:c:cut a:b:d:[dog] a:b:e:egg', ['a' => ['b' => ['c' => 'cut', 'd' => ['dog'], 'e' => 'egg']]]],
];

foreach ($tests as $i => $test) {
    test_tcon("Basic $i", $test[0], $test[1]);
}

$json_tests = [
    'null',
    'true',
    'false',
    ' null ',
    ' true ',
    ' false ',
    '["a", "b"]',
    '["a", "b", ["a"], {"a": "apple"}]',
    '{"a": {"b" : {"c":["cat", "cut"]}}}',
];

foreach ($json_tests as $i => $test) {
    test_tcon("JSON $i:", $test, json_decode($test, true));
}
