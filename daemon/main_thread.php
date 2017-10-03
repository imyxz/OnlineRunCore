<?php
/**
 * User: imyxz
 * Date: 2017-09-29
 * Time: 20:38
 * Github: https://github.com/imyxz/
 */
include_once "jobInfo.php";
include_once "curlRequest.php";
include_once "codeType.php";
include_once "function.php";
include_once "config.php";
global $Config;
$curl=new curlRequest();
$curl->setHeader("X-WORKER-ID: " . $Config['worker_id']);
$curl->setHeader("X-APIKEY: " . $Config['api_key']);
while(true)
{
    $return=$curl->get($Config['server_url'] . "workerAPI/getJobs/");
    $return=json_decode($return,true);
    foreach($return['job_id'] as $one)
    {
        echo "Start running $one...";
        exec("php runner_thread.php " . intval($one));
        echo "Done!\n";
    }
    sleep(1);
}