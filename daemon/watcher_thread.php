<?php
if(!isset($argv[1]) || !isset($argv[2])) return;
$jobid=intval($argv[1]);
$time=$argv[2];
if($time<1)
    $time=1;
sleep($time);
exec("lxc-stop -n $jobid");
