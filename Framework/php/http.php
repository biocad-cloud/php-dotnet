<?php

/**
 * Parses the request header into resource, headers and security code
 * (解析http请求头部)
 *
 * @param string $request The request
 * @return array Array containing the resource, headers and security code
 */
function parseRequestHeader($request) {
    $headers = [];

    foreach (explode("\r\n", $request) as $line) {
        if (strpos($line, ': ') !== false) {
            list($key, $value) = explode(': ', $line);

            $headers[trim($key)] = trim($value);
        }
    }

    return $headers;
}