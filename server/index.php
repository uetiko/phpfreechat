<?php

include_once dirname(__FILE__).'/lib/Slim/Slim/Slim.php';
include_once dirname(__FILE__).'/config.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

function debug($msg) {
  if (is_string($msg)) {
    file_put_contents(dirname(__FILE__).'/log/pfc.log', $msg."\n", FILE_APPEND);
  } else {
    file_put_contents(dirname(__FILE__).'/log/pfc.log', print_r($msg, true), FILE_APPEND);
  }
}

function GetPfcError($code, $jsonformat = true) {
  $errors = array();
  $errors[40301] = "Need authentication";
  $errors[40302] = "Login already used";
  $errors[40303] = "Wrong credentials";
  if (isset($errors[$code])) {
    $e = array('error' => $errors[$code], 'errorCode' => $code);
  } else {
    $e = array('error' => 'Unknown error #'.$code, 'errorCode' => $code);
  }
  return $jsonformat ? json_encode($e) : $e;
}

$req = $app->request();
$res = $app->response();
$res['X-Powered-By'] = 'phpfreechat-'.$GLOBALS['pfc_version'];

// connect custom user hooks
foreach ($GLOBALS['pfc_hooks'] as $hook_name => $hooks) {
  foreach ($hooks as $priority => $function) {
    $GLOBALS['pfc_hooks'][$hook_name][$priority] = $function($app, $req, $res);
  }
}

require 'routes/auth.php';
require 'routes/channels.php';
require 'routes/users.php';
require 'routes/utils.php';

$app->run();
