<?php

if(!isset($argv[1])) return;
$jobid=intval($argv[1]);
$second=4;
include_once "jobInfo.php";
include_once "jobResult.php";
include_once "curlRequest.php";
include_once "codeType.php";
include_once "function.php";
include_once "config.php";
global $Config;
$code_type=new codeType();
$job_info=new jobInfo();
$job_result=new jobResult();
$curl=new curlRequest();
$curl->setHeader("X-WORKER-ID: " . $Config['worker_id']);
$curl->setHeader("X-APIKEY: " . $Config['api_key']);
$response="";
for($i=0;$i<5 && !($response=$curl->get($Config['server_url'] . "workerAPI/beginRunningJob/job_id/$jobid"));$i++)
    sleep(1);
$response=json_decode($response,true);
$job_info->code_type=$response['job_info']['code_type'];
$job_info->program_stdin=$response['job_info']['program_stdin'];
$job_info->source_code=$response['job_info']['source_code'];
//$job_info=arrayToClass($response['job_info'],get_class($job_info));
$lxc_path="/home/runner/.local/share/lxc/$jobid";

exec("lxc-copy -s -n runner -N $jobid");

//mkdir("$lxc_path/delta0/home",0777,true);
//mkdir("$lxc_path/delta0/home/runner",0777,true);
exec("lxc-execute -n $jobid touch /home/runner/tmp");

file_put_contents("$lxc_path/delta0/home/runner/source.code",$job_info->source_code);
file_put_contents("$lxc_path/delta0/home/runner/input.txt",$job_info->program_stdin);
$target="c.sh";

switch($job_info->code_type)
{
    case $code_type->c:
        $target="c.sh";
        break;
    case $code_type->cpp:
        $target="cpp.sh";
        break;
    case $code_type->java:
        $target="java.sh";
        break;
    case $code_type->php7:
        $target="php7.sh";
        break;
    case $code_type->pascal:
        $target="pascal.sh";
        break;
    case $code_type->python3:
        $target="python3.sh";
        break;
}
exec("php watcher_thread.php $jobid $second > /dev/null &");//启动监视进程
exec("cgexec -g cpu,memory:runner_limit lxc-execute -n $jobid /etc/$target -o /dev/null > /dev/null");
$job_result->compile_error=_readFile("$lxc_path/delta0/home/runner/compile_error.txt");
$job_result->program_stdout=_readFile("$lxc_path/delta0/home/runner/output.txt");
$job_result->time_info=_readFile("$lxc_path/delta0/home/runner/time.txt");
$job_result->time_usage=intval(doubleval(getSubStr($job_result->time_info,"(h:mm:ss or m:ss): 0:","\n",0))*1000);
$job_result->mem_usage=intval(doubleval(getSubStr($job_result->time_info,"Maximum resident set size (kbytes): ","\n",0))*1024);
$job_result->compile_state=intval(_readFile("$lxc_path/delta0/home/runner/compile.state"));
$job_result->run_state=intval(_readFile("$lxc_path/delta0/home/runner/run.state"));
$json=classToJson($job_result);
$curl->post($Config['server_url'] . "workerAPI/uploadJobResult/job_id/$jobid",$json);
exec("lxc-destroy -n $jobid");
function _readFile($path)
{
    if(!file_exists($path))
        return "";
    if(@filesize($path)>64*1024)
        return "Output Limit Exceed";
    return @file_get_contents($path);
}
function getSubStr($str,$needle1,$needle2,$start_pos)
{
    $pos1=strpos($str,$needle1,$start_pos);
    if($pos1===false) return false;
    $pos2=strpos($str,$needle2,$pos1+strlen($needle1));
    if($pos2===false)   return false;
    return substr($str,$pos1+strlen($needle1),$pos2-$pos1-strlen($needle1));
}