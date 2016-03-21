<?php

session_start();
@$_SESSION['foo']++;
sleep(1);
echo $_SESSION['foo'];
