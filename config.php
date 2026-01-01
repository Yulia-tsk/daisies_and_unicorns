<?php

$dotenv = parse_ini_dotenv('.env');
function parse_ini_dotenv($file) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (preg_match('/^([\w_]+)=(.+)$/', $line, $matches)) {
            $data[$matches[1]] = trim($matches[2], '"\'');
        }
    }
    return $data;
}
return $dotenv;