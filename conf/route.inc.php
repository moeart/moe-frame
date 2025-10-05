<?php

// For hostname group.
$MoeRouter->H([
    "hostname-a.example.com",
    "hostname-b.example.net:8038",
    "192.168.0.1:8081"
], function($MoeRouter) 
{
    $middleware = [
        "cidr_whitelist" => [
            "192.168.1.0/24",
            "10.0.0.0/8"
        ]
    ];
    $MoeRouter->R('/', 'ExampleApp@Hello');
    $MoeRouter->R('/hostname/group', 'ExampleApp@Hello', $middleware); // with middleware filter
});

// For regex.
$MoeRouter->R('/regex/?[acg,draw]', 'ExampleApp@Hello');

// For middleware.
$MoeRouter->R('/middleware/cidr', 'ExampleApp@Hello', [
    "cidr_whitelist" => [    // cidr whitelist filter
        "192.168.1.0/24",
        "10.0.0.0/8"
    ],
    "hosts_whitelist" => [   // hostname whitelist filter
        "www.example.com"
    ]
]);

// For regular. (lower)
$MoeRouter->R('/', 'ExampleApp@Hello');
$MoeRouter->R('/hello', 'ExampleApp@Hello');


?>