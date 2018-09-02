<?php


// Create the server object and listen 127.0.0.1:9501
$server = new swoole_http_server("127.0.0.1", 8080);


// Register the function for the event `receive`
$server->on('request', function($request, swoole_http_response $response){
    $response->end("aaaaa");
});

// Start the server
$server->start();
