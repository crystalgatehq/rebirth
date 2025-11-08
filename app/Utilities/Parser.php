<?php

/**
 * Parser Helper Class
 *
 * @category Parser
 * @author Wagura Maurice
 * @link https://gist.github.com/wagura-maurice/Parser.php
 */

const _l = '{';
const _r = '}';

if (! function_exists('_parse')) {
    /**
     * > It takes a template and a data array and returns a string
     * 
     * @param template The template string to parse.
     * @param data The data to be parsed.
     */
    function _parse($template, $data): string
    {
        if ($template === '') {
            return false;
        }

        $replace = [];
        
        foreach ($data as $key => $val) {
            $replace = array_merge($replace, is_array($val) ? _parse_pair($key, $val, $template) : _parse_single($key, (string) $val));
        }

        unset($data);

        $template = strtr($template, $replace);

        return $template;
    }
}

if (! function_exists('_parse_single')) {
    /**
     * It takes a key and a value, and returns an array with the key wrapped in curly braces and the
     * value cast to a string.
     * 
     * @param key The key of the array.
     * @param val The value to be parsed.
     * 
     * @return array An array with the key being the value of  and the value being the value of
     * .
     */
    function _parse_single($key, $val): array
    {
        return [_l.$key._r => (string) $val];
    }
}

if (! function_exists('_parse_pair')) {
    /**
     * It takes a variable name, an array of data, and a string, and returns an array of replacements
     * 
     * @param variable The name of the variable to parse.
     * @param data The data to be parsed.
     * @param string The string to parse.
     * 
     * @return array An array of the matches.
     */
    function _parse_pair($variable, $data, $string): array
    {
        $replace = [];

        preg_match_all('#'.preg_quote(_l.$variable._r).'(.+?)'.preg_quote(_l.'/'.$variable._r).'#s', $string, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $str = '';
            
            foreach ($data as $row) {
                $temp = [];

                foreach ($row as $key => $val) {
                    if (is_array($val)) {
                        $pair = _parse_pair($key, $val, $match[1]);
                        if (! empty($pair)) {
                            $temp = array_merge($temp, $pair);
                        }

                        continue;
                    }

                    $temp[_l.$key._r] = $val;
                }

                $str .= strtr($match[1], $temp);
            }

            $replace[$match[0]] = $str;
        }

        return $replace;
    }
}
