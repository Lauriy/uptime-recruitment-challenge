<?php

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

// Our web handlers

$app->get('/', function () use ($app) {
    $feedContent = file_get_contents('https://flipboard.com/@raimoseero/feed-nii8kd0sz?rss');
    $xml = new SimpleXmlElement($feedContent);
    //var_dump($xml);

    return $app['twig']->render('index.twig', array(
        'feed' => $xml
    ));
});

$app->get('/mercury-parse/{urlBase64}', function ($urlBase64) use ($app) {
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => 'X-Api-Key: ' . getenv('MERCURY_API_KEY')
        ]
    ];

    $context = stream_context_create($opts);
    $mercuryParsed = file_get_contents('https://mercury.postlight.com/parser?url=' . base64_decode($urlBase64), false, $context);

    return $mercuryParsed;
});

$app->run();
