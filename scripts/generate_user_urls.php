<?php

$urls = [];
$users = 1000;
for ($i = 1; $i < count($argv); $i++) {
    if (is_numeric($argv[$i])) {
        $users = (int)$argv[$i];
    } else {
        $urls[] = $argv[$i];
    }
}

foreach ($urls as $url) {
    for ($i = 0; $i < $users; $i++) {
        echo $url . "?userId=" . $i . "\n";
    }
}
