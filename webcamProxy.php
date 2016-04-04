<?php

function datacb($ch, $data) {
    echo $data;
    return strlen($data);
}

function Fetch($url, $config) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERPWD, $config['username'].":".$config['password']); 
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_BUFFERSIZE, 8096);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, "datacb"); 
    $f = curl_exec($ch);
    return $f;
}

function UpdateConfigStrings($currentConfig) {
  $newConfig = array();
  foreach($currentConfig as $key=>$value) {
    $value = str_replace("{USER}", $currentConfig['username'], $value);
    $value = str_replace("{PASS}", $currentConfig['password'], $value);
    $newConfig[$key] = $value;
  }
  return $newConfig;
}


##MAIN:
if (!function_exists('curl_init')) {
  echo "php curl support must be installed!!!";
  exit(1);
}
if(!isset($_GET['webcam']) || !isset($_GET["cmd"])) {
  echo "require a webcam and a cmd param!";
  exit(1);
}
$tmp = file_get_contents("webcamConfig.ini");
$configFile = parse_ini_string($tmp, true);
$currentConfig = $configFile[$_GET['webcam']];

header("Connection: close");
header("Content-Type: multipart/x-mixed-replace;boundary=".$currentConfig['boundary']);


$currentConfig = UpdateConfigStrings($currentConfig);

if($currentConfig['logFile'] !== false) {
    $fp = fopen($currentConfig['logFile'], 'a');
    fwrite($fp, "IN FROM CLIENT:\n".print_r($_GET, true)."\n----------\n");
    fwrite($fp, print_r($currentConfig, true)."\n----------\n");
    fwrite($fp, $currentConfig['url']."/".$currentConfig[$_GET['cmd']]."\n----------\n");
    fclose($fp);
}

$out = Fetch($currentConfig['url']."/".$currentConfig[$_GET['cmd']], $currentConfig);
