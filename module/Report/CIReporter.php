<?php

use Codeception\Module\ImageDeviationException;

class CIReporter implements Reporter
{

    private $failues = array();

    private $templateFile;
    private $templateVars = array();

    public function __construct(array $config)
    {
        if (array_key_exists('templateVars', $config)) {
            $this->templateVars = $config["templateVars"];
        }
        if (array_key_exists('templateFile', $config)) {
            $this->templateFile = $config["templateFile"];
        }
        if (array_key_exists('logFile', $config)) {
            $this->logFile = $config["logFile"];
        }
    }

    public function processFailure(ImageDeviationException $exception)
    {
        $this->failues[] = $exception;
    }

    public function finish()
    {
        $failedTests = $this->failues;
        $vars = $this->templateVars;

        ob_start();

        include_once $this->templateFile;
        $reportContent = ob_get_contents();
        ob_clean();

        file_put_contents($this->logFile, $reportContent);
    }
}