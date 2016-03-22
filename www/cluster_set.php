<?php

$nodes = include "/var/www/nodes.php";

shuffle($nodes);

$cluster = new RedisCluster(null, $nodes, 0.5, 0.5);

$userId = isset($_GET['userId']) ? (int)$_GET['userId'] : rand(0, 16384);
$userId = "user" . $userId;

$i = (int)$cluster->get("{$userId}foo");
$i++;

$ret = $cluster->set("{$userId}foo", $i);

if ($ret === false) {
    header("HTTP/1.1 560 Write Fail");
    echo '';
    exit();
}

$value = $cluster->get("{$userId}foo");

if ($value != $i) {
    header("HTTP/1.1 561 Dirty or Wrong Read");
    echo "Value $value Should be $i";
    exit();
}

echo "Value: " . $value . "<br />";
echo "User: " . $userId . "<br />";
