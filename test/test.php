<?php

require("../src/RLock.class.php");

$config = [
            'servers' => [
                [
                    'host' => '192.168.0.214',
                    'port' =>  6379,
                    'time_out' => 10,
                ],
            ],
        ];
RLock::init($config);
RLock::synchronized('name',function() {
	for ($index = 0;$index < 10;$index++) {
		echo getmypid();
	}
});