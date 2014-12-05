<?php

use Codeception\Module\ImageDeviationException;

interface Reporter
{
    public function processFailure(ImageDeviationException $exception);
    public function finish();
}