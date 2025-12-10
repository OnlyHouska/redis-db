<?php
$redis = new Redis();
$redis->connect('redis', 6379);

if (!$redis->ping()) {
    throw new Exception('Cannot connect to Redis');
}
