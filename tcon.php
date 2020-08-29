<?php

namespace tcon;
class Exception extends \Exception {}

const MAX_DEPTH = 512;

const STRING_ENCLOSURE = 1;
const OBJECT_START     = 2;
const OBJECT_END       = 3;
const KEY_SEPARATOR    = 4;
const VAL_SEPARATOR    = 5;
const ROW_START        = 6;
const ROW_END          = 7;

const SPECIAL_CHARS = [
    '"' => STRING_ENCLOSURE,
    "'" => STRING_ENCLOSURE,
    '[' => OBJECT_START,
    ']' => OBJECT_END,
    '{' => OBJECT_START,
    '}' => OBJECT_END,
    ',' => VAL_SEPARATOR,
    ':' => KEY_SEPARATOR,
    '>' => ROW_START,
    "\r" => ROW_END,
    "\n" => ROW_END,
];

function tcon_parse($str, $as_array = true) {
    $vals = parse_array($str);
    
    if ($as_array) return $vals;
    
    switch (count($vals)) {
        case 0: return null; // if nothing found, return null
        case 1: return array_key_exists(0, $vals) ? $vals[0] : $vals; // if only 1 thing found, without a specified key, return that thing
        default: return $vals; // if many things found, return implied array
    }
}

function parse_array($str, &$i = 0, $depth = 0, $row_end = false) {
    if (++$depth > MAX_DEPTH) throw new Exception('too deep');
    
    $vals = [];
    $strings = [];
    $has_key = false;
    $parse_last_str = false;
    
    while (isset($str[$i])) {
        $next_val = read_next_val($str, $i, $depth, $enclosed, $row_end);
        
        if ($next_val === null or $next_val === OBJECT_END) {
            break;
        } else if ($next_val === KEY_SEPARATOR) {
            if (!isset($strings[0]) or $has_key) {
                throw new Exception('No key defined');
            }
            
            $has_key = true;
        } else {
            store_val($next_val, $vals, $strings, $has_key, $parse_last_str, $enclosed);
            $has_key = false;
        }
    }
    
    if ($has_key) throw new Exception('No value defined');
    
    if (isset($strings[0])) add_property(array_pop($strings), $vals, $strings, $parse_last_str);
    
    return $vals;
}

function read_next_val($str, &$i, $depth, &$enclosed, $row_end = false) {
    $enclosed = true;
    
    while (isset($str[$i])) {
        $char = $str[$i++];
        
        if ($row_end and (SPECIAL_CHARS[$char] ?? 0) === ROW_END) return null;
        
        if (ctype_space($char)) continue;
        
        switch (SPECIAL_CHARS[$char] ?? 0) {
            case VAL_SEPARATOR:    continue 2; // treat as whitespace
            case KEY_SEPARATOR:    return KEY_SEPARATOR;
            case OBJECT_END:       return OBJECT_END;
            case OBJECT_START:     return parse_array($str, $i, $depth);
            case ROW_START:        return parse_array($str, $i, $depth, true);
            case STRING_ENCLOSURE: return parse_enclosed_string($str, $i, $char);
            default: $enclosed = false; return parse_unenclosed_string($str, $i);
        }
    }
    
    return null; // end of string
}

function parse_enclosed_string($str, &$i, $enclosure) {
    $start = $i;
    $escaped = false;
    $len = 0;
    
    for (; isset($str[$i]); ++$i) {
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

function parse_unenclosed_string($str, &$i) {
    // already read first char, start from there
    $start = $i - 1;
    $len = 1;
    
    for (; isset($str[$i]); ++$i) {
        if (isset(SPECIAL_CHARS[$str[$i]]) or ctype_space($str[$i])) {
            break;
        }
        
        ++$len;
    }
    
    return substr($str, $start, $len);
}

function store_val($val, &$vals, &$strings, $has_key, &$parse_last_str, $enclosed) {
    if ($has_key) {
        if (is_string($val)) {
            $strings[] = $val;
        } else {
            add_property($val, $vals, $strings, false);
        }
    } else {
        if (isset($strings[0])) {
            add_property(array_pop($strings), $vals, $strings, $parse_last_str);
        }
        
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
        $target =& $vals;
        $last_key = array_pop($keys);
        
        foreach ($keys as $key) {
            if (!isset($target[$key]) or !is_array($target[$key])) {
                $target[$key] = [];
            }
            
            $target =& $target[$key];
        }
        
        $target[$last_key] = $val;
        $keys = [];
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
