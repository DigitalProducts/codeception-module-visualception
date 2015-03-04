<?php

use Codeception\Module\ImageDeviationException;

class NullReporter implements Reporter {

    private $currentImageDir;

    public function __construct(array $config)
    {
    }

    public function processFailure(ImageDeviationException $exception)
    {
    }

    public function finish()
    {

    }
}