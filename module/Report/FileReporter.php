<?php

use Codeception\Module\ImageDeviationException;

class FileReporter implements Reporter {

    private $currentImageDir;

    public function __construct(array $config)
    {
        $this->currentImageDir = $config['currentImageDir'];

        if( !is_dir($this->currentImageDir)) {
            mkdir ($this->currentImageDir, 0777, true);
        }
    }

    public function processFailure(ImageDeviationException $exception)
    {
        $currentImage = $exception->getComparisonResult()->getCurrentImage();
        $currentImage->writeimage($this->currentImageDir . DIRECTORY_SEPARATOR . $exception->getIdentifier() . '.png');
    }

    public function finish()
    {

    }
}