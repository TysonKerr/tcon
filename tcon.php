<?php

namespace tcon;
class Exception extends \Exception {}

/*
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
*/

class Tcon {
    const MAX_DEPTH = 512;

    const STRING_ENCLOSURE = 1;
    const OBJECT_START     = 2;
    const OBJECT_END       = 3;
    const KEY_SEPARATOR    = 4;

    const SPECIAL_CHARS = [
        '"' => self::STRING_ENCLOSURE,
        "'" => self::STRING_ENCLOSURE,
        '[' => self::OBJECT_START,
        ']' => self::OBJECT_END,
        '{' => self::OBJECT_START,
        '}' => self::OBJECT_END,
        ',' => 5,
        ':' => self::KEY_SEPARATOR,
    ];
    
    public static function parse($str, $as_array = true) {
        $vals = (new self($str))->parse_array();
        
        if ($as_array) return $vals;
        
        switch (count($vals)) {
            case 0: return null; // if nothing found, return null
            case 1: return array_key_exists(0, $vals) ? $vals[0] : $vals; // if only 1 thing found, without a specified key, return that thing
            default: return $vals; // if many things found, return implied array
        }
    }
    
    private function __construct($str, $i = 0, $depth = 0) {
        $this->str = $str;
        $this->i = $i;
        $this->depth = $depth;
        $this->vals = [];
        $this->strings = [];
        $this->parse_last_str = false;
    }
    
    private function parse_array() {
        if ($this->depth++ > self::MAX_DEPTH) throw new Exception('too deep');
        
        $val_has_key = false;
        
        while (isset($this->str[$this->i])) {
            $next = $this->read_next_val($enclosed);
            
            if ($next === null or $next === self::OBJECT_END) {
                break;
            } else if ($next === self::KEY_SEPARATOR) {
                if (!isset($this->strings[0]) or $val_has_key) {
                    throw new Exception('No key defined');
                }
                
                $val_has_key = true;
            } else {
                $this->store_val($next, $val_has_key, $enclosed);
                $val_has_key = false;
            }
        }
        
        if ($val_has_key) throw new Exception('No value defined');
        
        if (isset($this->strings[0])) {
            $this->add_property(array_pop($this->strings));
        }
        
        return $this->vals;
    }
    
    private function read_next_val(&$enclosed) {
        $enclosed = true;
        
        while (isset($this->str[$this->i])) {
            $char = $this->str[$this->i++];
            
            if (ctype_space($char) or $char === ',') continue;
            
            switch (self::SPECIAL_CHARS[$char] ?? 0) {
                case self::KEY_SEPARATOR:    return self::KEY_SEPARATOR;
                case self::OBJECT_END:       return self::OBJECT_END;
                case self::OBJECT_START:     return $this->parse_sub_array();
                case self::STRING_ENCLOSURE: return $this->parse_enclosed_string($char);
                default:  $enclosed = false; return $this->parse_unenclosed_string();
            }
        }
        
        return null; // end of string
    }
    
    private function parse_sub_array() {
        $parser = new self($this->str, $this->i, $this->depth);
        $val = $parser->parse_array();
        $this->i = $parser->i;
        return $val;
    }
    
    private function parse_enclosed_string($enclosure) {
        $start = $this->i;
        $escaped = false;
        $len = 0;
        
        for (; isset($this->str[$this->i]); ++$this->i) {
            if ($escaped) {
                $escaped = false;
            } else if ($this->str[$this->i] === $enclosure) {
                ++$this->i;
                break;
            } else if ($this->str[$this->i] === '\\') {
                $escaped = true;
            }
            
            ++$len;
        }
        
        return substr($this->str, $start, $len);
    }
    
    private function parse_unenclosed_string() {
        // we already read the first character, start substring from there
        $start = $this->i - 1;
        $len = 1;
        
        for (; isset($this->str[$this->i]); ++$this->i) {
            $char = $this->str[$this->i];
            
            if (isset(self::SPECIAL_CHARS[$char]) or ctype_space($char)) {
                break;
            }
            
            ++$len;
        }
        
        return substr($this->str, $start, $len);
    }

    private function store_val($val, $has_key, $enclosed) {
        if ($has_key) {
            if (is_string($val)) {
                $this->strings[] = $val;
            } else {
                $this->add_property($val);
            }
        } else {
            if (isset($this->strings[0])) {
                $this->add_property(array_pop($this->strings));
            }
            
            if (is_string($val)) {
                $this->strings[] = $val;
            } else {
                $this->vals[] = $val;
            }
        }
        
        $this->parse_last_str = !$enclosed;
    }

    private function add_property($val) {
        if ($this->parse_last_str and is_string($val)) {
            $val = self::parse_value($val);
        }
        
        if (isset($this->strings[0])) {
            while (isset($this->strings[1])) {
                $val = [array_pop($this->strings) => $val];
            }
            
            $this->vals[array_pop($this->strings)] = $val;
        } else {
            $this->vals[] = $val;
        }
    }

    private static function parse_value($val) {
        $lower = strtolower($val);
        
        switch ($lower) {
            case 'null':  return null;
            case 'true':  return true;
            case 'false': return false;
        }
        
        return $val;
    }
}
