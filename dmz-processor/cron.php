#!/usr/bin/php
<?php

require_once __DIR__ . '/../get_host_info.inc';
require_once __DIR__ . '/../rabbitMQLib.inc';

$connection = new rabbitMQClient(__DIR__ . '/../testRabbitMQ.ini', 'testServer3'); //connection to the broker

$request['type']= "weekly_charts";

$connection->publish($request);
