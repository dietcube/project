<?php
/**
 *
 */

namespace SampleApp;

use Dietcube\Application as DCApplication;
use Pimple\Container;
use SampleApp\Service\SampleService;

class Application extends DCApplication
{
    public function init(Container $container)
    {
        // do something before boot
    }

    public function config(Container $container)
    {
        // setup container or services here
        $container['service.sample'] = function () use ($container)  {
            $sample_service = new SampleService();
            $sample_service->setLogger($container['logger']);

            return $sample_service;
        };
    }
}
