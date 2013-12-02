<?php

use Symfony\Component\HttpFoundation\Request;
use Bab\RabbitMqGraph\Client;
use Bab\RabbitMqGraph\Graph;

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig');
});

$app->post('/', function (Request $request) use ($app) {
    $definitions = $request->request->get('definitions', null);
    $definitions = json_decode($definitions, true);
    if (!is_array($definitions)) {
        $app['session']->getFlashBag()->add('error', 'Invalid definitions');

        return $app->redirect('/');
    }

    $graph = new Graph(uniqid(), $definitions);

    return $app['twig']->render('result.html.twig', array(
        'graph' => $graph->render(),
    ));
});

return $app;
