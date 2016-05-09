<?php

$nodes = include "/var/www/nodes.php";

shuffle($nodes);

$slots = array();
$nodeState = array();
$slaves = array();

foreach ($nodes as $node) {
    list ($host, $port) = explode(":", $node);
    exec("redis-cli -h $host -p $port cluster nodes", $lines, $return);

    if ($return != 0) {
        continue;
    }

    foreach ($lines as $line) {
        $state = explode(" ", $line);

        $currentStates = explode(',', $state[2]);

        if (in_array('fail', $currentStates) || in_array('fail?', $currentStates)) {
            $nodeState[$state[1]] = ['color' => 'red', 'type' => in_array('master', $currentStates) ? 'master' : 'slave'];
        } else {
            $nodeState[$state[1]] = ['color' => 'green', 'type' => in_array('master', $currentStates) ? 'master' : 'slave'];

            if (in_array('master', $currentStates)) {
                list($start, $end) = explode('-', $state[8]);
                $slots = array_merge($slots, range($start, $end));
            }

            if (in_array('slave', $currentStates)) {
                $masterId = $state[3];
                $slaveId = $state[0];
                $slaves[$masterId][] = $slaveId;
            }
        }
    }

    break;
}

$clusterState = 'green';
$metrics = array();
if (count($slaves) < 3) { // we only have less than 3 masters that actually have slaves
    $clusterState = 'yellow';
}
if (count($slots) < 16384) {
    $clusterState = 'red';
}
printf("CLUSTER STATE: %s\n", $clusterState);
foreach ($nodeState as $node => $data) {
    printf("%s: %s (%s)\n", $node, $data['color'], $data['type']);
}
