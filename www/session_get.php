<?php
$nodes = include "/var/www/nodes.php";
shuffle($nodes);
$save_path = ['seed' => $nodes, 'read_timeout' => 0.5, 'timeout' => 0.5, 'failover' => 'error'];

ini_set('session.save_path', http_build_query($save_path));

if (isset($_GET['userId'])) {
    session_id("user" . $_GET['userId']);
}

session_start();

echo $_SESSION['num'];

if (isset($_GET['num']) && $_GET['num'] != $_SESSION['num']) {
    header("HTTP/1.1 470 Invalid Session");
    echo '{}';
    exit();
}
