<?php

namespace tcon;
class Exception extends \Exception {}

const MAX_DEPTH = 512;

const STRING_ENCLOSURE = 1;
const OBJECT_START     = 2;
const OBJECT_END       = 3;
const KEY_SEPARATOR    = 4;

const SPECIAL_CHARS = [
    '"' => STRING_ENCLOSURE,
    "'" => STRING_ENCLOSURE,
    '[' => OBJECT_START,
    ']' => OBJECT_END,
    '{' => OBJECT_START,
    '}' => OBJECT_END,
    ',' => 5,
    ':' => KEY_SEPARATOR,
];

function tcon_parse($str, $debug = false) {
    $vals = parse_array($str, strlen($str), $debug);
    
    switch (count($vals)) {
        case 0: return null; // if nothing found, return null
        case 1: return (isset($vals[0]) or array_key_exists(0, $vals)) ? $vals[0] : $vals; // if only 1 thing found, without a specified key, return that thing
        default: return $vals; // if many things found, return implied array
    }
}

function parse_array($str, $max_i, $debug = false, &$i = 0, $depth = 0) {
    if (++$depth > MAX_DEPTH) throw new Exception('too deep');
    
    $vals = [];
    $strings = [];
    $prev_val_was_key_sep = false;
    $parse_last_str = false;
    
    while ($i < $max_i) {
        $next_val = read_next_val($str, $max_i, $debug, $i, $depth, $enclosed);
        
        if ($next_val === null or $next_val === OBJECT_END) {
            break;
        } else if ($next_val === KEY_SEPARATOR) {
            if (!isset($strings[0]) or $prev_val_was_key_sep) throw new Exception('No key defined');
            
            $prev_val_was_key_sep = true;
        } else {
            store_val($next_val, $vals, $strings, $prev_val_was_key_sep, $parse_last_str, $enclosed);
            $prev_val_was_key_sep = false;
        }
    }
    
    if ($prev_val_was_key_sep) throw new Exception('No value defined');
    
    if (isset($strings[0])) add_property(array_pop($strings), $vals, $strings, $parse_last_str);
    
    return $vals;
}

function read_next_val($str, $max_i, $debug, &$i, $depth, &$enclosed) {
    $enclosed = true;
    
    for (; $i < $max_i; ++$i) {
        $char = $str[$i];
        
        if (ctype_space($char) or $char === ',') continue;
        
        switch (SPECIAL_CHARS[$char] ?? 0) {
            case KEY_SEPARATOR:    ++$i; return KEY_SEPARATOR;
            case OBJECT_END:       ++$i; return OBJECT_END;
            case OBJECT_START:     ++$i; return parse_array($str, $max_i, $debug, $i, $depth);
            case STRING_ENCLOSURE: ++$i; return parse_enclosed_string($str, $max_i, $i, $char);
            default:  $enclosed = false; return parse_unenclosed_string($str, $max_i, $i);
        }
    }
    
    return null; // end of string
}

function parse_enclosed_string($str, $max_i, &$i, $enclosure) {
    $start = $i;
    $escaped = false;
    $len = 0;
    
    for (; $i < $max_i; ++$i) {
        if ($escaped) {
            $escaped = false;
        } else if ($str[$i] === $enclosure) {
            ++$i;
            break;
        } else if ($str[$i] === '\\') {
            $escaped = true;
        }
        
        ++$len;
    }
    
    return substr($str, $start, $len);
}

function parse_unenclosed_string($str, $max_i, &$i) {
    $start = $i;
    $len = 0;
    
    for (; $i < $max_i; ++$i) {
        if (isset(SPECIAL_CHARS[$str[$i]]) or ctype_space($str[$i])) {
            break;
        }
        
        ++$len;
    }
    
    return substr($str, $start, $len);
}

function store_val($val, &$vals, &$strings, $prev_val_was_key_sep, &$parse_last_str, $enclosed) {
    if ($prev_val_was_key_sep) {
        if (is_string($val)) {
            $strings[] = $val;
        } else {
            add_property($val, $vals, $strings, false);
        }
    } else {
        if (isset($strings[0])) add_property(array_pop($strings), $vals, $strings, $parse_last_str);
        
        if (is_string($val)) {
            $strings[] = $val;
        } else {
            $vals[] = $val;
        }
    }
    
    $parse_last_str = !$enclosed;
}

function add_property($val, &$vals, &$keys, $parse_last_str) {
    if ($parse_last_str) $val = parse_value($val);
    
    if (isset($keys[0])) {
        while (isset($keys[1])) {
            $val = [array_pop($keys) => $val];
        }
        
        $vals[array_pop($keys)] = $val;
    } else {
        $vals[] = $val;
    }
}

function parse_value($val) {
    $lower = strtolower($val);
    
    switch ($lower) {
        case 'null':  return null;
        case 'true':  return true;
        case 'false': return false;
    }
    
    return $val;
}
