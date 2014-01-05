<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__.'/../views',
]);

$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/settings.yml'));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig');
});

$app->get('/timeline', function () use ($app) {

  $config = [
    'consumer_key'        => $app['config']['twitter']['consumer_key'],
    'consumer_secret'     => $app['config']['twitter']['consumer_secret'],
    'access_token'        => $app['config']['twitter']['access_token'] ?: null,
    'access_token_secret' => $app['config']['twitter']['access_token_secret'] ?: null,
  ];

  $ttools = new \TTools\App($config);
  $screen_name = $app['config']['user_timeline']['screen_name'] ?: null;
  $limit = $app['config']['user_timeline']['limit'] ?: 10;
  $stream = $ttools->getUserTimeline(null, $screen_name, $limit);

  return $app['twig']->render('timeline.html.twig', [
      'stream' => $stream,
  ]);

});

$app->run();