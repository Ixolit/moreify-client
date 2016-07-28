#!/usr/bin/php
<?php

use Ixolit\Moreify\HTTP\Guzzle\GuzzleHTTPClientAdapter;

require_once(__DIR__ . '/../vendor/autoload.php');

$project   = $_SERVER['argv'][1];
$password  = $_SERVER['argv'][2];
$recipient = $_SERVER['argv'][3];
$message   = $_SERVER['argv'][4];

$client = new \Ixolit\Moreify\MoreifyClient($project, $password, new GuzzleHTTPClientAdapter());
$response = $client->sendSMS($recipient, $message);
var_dump($response);
