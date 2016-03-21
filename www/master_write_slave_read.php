<?php

$nodes = array (
  0 => '46.101.104.112:7000',
  1 => '46.101.104.112:7001',
  2 => '46.101.104.112:7002',
  3 => '46.101.111.180:7000',
  4 => '46.101.111.180:7001',
  5 => '46.101.111.180:7002',
);
shuffle($nodes);
$cluster = new RedisCluster(null, $nodes, 0.5, 0.5);

$value = isset($argv[1]) ? $argv[1] : "hello world";

$writeNotAcked = 0;
$invalidWrite = 0;
$failure = 0;
$ack = 0;

define('SECOND', 1000000);

for ($i = 0; $i < 500; $i++) {
    usleep(SECOND * 0.05);
    $userId = "user" . $i;
    $cluster = null;
    try {
        shuffle($nodes);
        $cluster = new RedisCluster(null, $nodes, 0.5, 0.5);
        $ret = $cluster->set("{$userId}foo", $value);

        if ($ret === false) {
            $writeNotAcked++;
        } else {
            $ack++;
        }
    } catch (\Exception $e) {
        $failure++;
    } finally {
        if ($cluster) {
            $cluster->close();
        }
    }
}

for ($i = 0; $i < 500; $i++) {
    $userId = "user" . $i;
    $cluster = null;
    try {
        shuffle($nodes);
        $cluster = new RedisCluster(null, $nodes, 0.5, 0.5);
        $dbValue = $cluster->get("{$userId}foo");

        if ($dbValue != $value) {
            $invalidWrite++;
        }
    } catch (\Exception $e) {
        $failure++;
    } finally {
        if ($cluster) {
            $cluster->close();
        }
    }
}

printf("Ack: %d\nWrite not acknowlegded: %d\nInvalid Write: %d\nFailures: %d\n", $ack, $writeNotAcked, $invalidWrite, $failure);

