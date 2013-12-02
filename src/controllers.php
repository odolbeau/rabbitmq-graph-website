<?php

use Symfony\Component\HttpFoundation\Request;
use Bab\RabbitMqGraph\Client;
use Bab\RabbitMqGraph\Graph;
use Symfony\Component\Process\ProcessBuilder;

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

    $graph = new Graph('my_graph', $definitions);
    $dotContent = $graph->render();

    $tmpFile   = tempnam(sys_get_temp_dir(), 'bbc_');
    $tmpTarget = tempnam(sys_get_temp_dir(), 'bbc_');
    file_put_contents($tmpFile, $dotContent);

    $process = ProcessBuilder::create(array('dot', '-Tsvg', '-o'.$tmpTarget, $tmpFile))->getProcess();
    $process->run();
    if (!$process->isSuccessful()) {
        throw new \RuntimeException($process->getErrorOutput());
    }

    $content = file_get_contents($tmpTarget);
    unlink($tmpFile);
    unlink($tmpTarget);

    return $app['twig']->render('result.html.twig', array(
        'graph' => $content,
    ));
});

return $app;
