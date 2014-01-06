<?php

require_once __DIR__.'/../vendor/autoload.php';

/**********************************************
* Copy config/settings.yml.dist to config/settings.yml
* Update using the keys of your twitter application. 
*
* consumer_key: application consumer key
* consumer_secret: application consumer secret
* access_token: user access token
* access_token_secret: user access secret
*
* Get the keys you need to register your application at http://dev.twitter.com
***********************************************/

$app = new Silex\Application();

$app->register(new Silex\Provider\FormServiceProvider());

$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__.'/../views',
]);

$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.messages' => array(),
));

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

$app->match('/compose', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

  $form = $app['form.factory']->createBuilder('form')
      ->add('text', 'textarea', [
          'attr' => [ 
            'placeholder' => 'Compose new Tweet...'
          ],
          'label' => false
        ])
      ->add('image', 'file', [
          'label' => false
        ])
      ->add('submit', 'submit')
      ->getForm();

  return $app['twig']->render('compose.html.twig', ['form' => $form->createView()]);
});

$app->run();