<?php

$tests = [
    ['', null],
    [' ', null],
    [' ,,', null],
    [' apple ', 'apple'],
    ['a\tb', "a\tb"],
    ['a\t\r\nb', "a\t\r\nb"],
    ['a\:\>b', 'a:>b'],
    ['1', 1],
    ['1 2', [1, 2]],
    ['0 0.0 00.00 1 -1 -2.0 3.1 +4 -4.2e2 5.6e-1', [0, 0.0, 0.0, 1, -1, -2.0, 3.1, 4, -4.2e2, 5.6e-1]],
    [' aa `a\'b\'c"d"e` ', ['aa', 'a\'b\'c"d"e']],
    ['a b c', ['a', 'b', 'c']],
    ['a\ b \ ', ['a b', ' ']],
    [' apple,banana ', ['apple', 'banana']],
    [' "abc def \'ghi\' " ', "abc def 'ghi' "],
    [" 'abc def \"ghi\" ' ", 'abc def "ghi" '],
    [' "abc\"def\"ghi" ', 'abc"def"ghi'],
    [' ab\ cd\ ef ', 'ab cd ef'],
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
