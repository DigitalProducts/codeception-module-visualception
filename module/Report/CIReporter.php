<?php

use Codeception\Module\ImageDeviationException;

class CIReporter implements Reporter {

    private $failues = array();

    private $templateFile;
    private $templateVars = array();

    public function __construct(array $config)
    {
        $this->templateVars = $config["templateVars"];
        $this->templateFile = $config["templateFile"];
        $this->logFile = $config["logFile"];
    }

    public function processFailure(ImageDeviationException $exception)
    {
        $this->failues[] = $exception;
    }

    public function finish()
    {
        $failedTests = $this->failues;
        $vars = $this->templateVars;

        var_dump( $this->templateFile);

        ob_start();

        include_once $this->templateFile;
        $reportContent = ob_get_contents();
        ob_clean();

        file_put_contents($this->logFile, $reportContent);
    }
}