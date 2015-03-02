<?php

use Codeception\Module\ImageDeviationException;

class CIReporter implements Reporter {

    private $failures = array();

    private $templateFile;
    private $templateVars = array();
    private $defaultTemplateVars = array(
        'logo' => 'http://www.thewebhatesme.com/VisualCeption/compare.png',
        'color' => '#e5e6e6',
        'text' => '',
    );

    public function __construct(array $config)
    {
        $this->templateVars = array_merge( $this->defaultTemplateVars, (array) $config["templateVars"] );
        $this->templateFile = $config["templateFile"];
        $this->logFile = $config["logFile"];
    }

    public function processFailure(ImageDeviationException $exception)
    {
        $this->failures[] = $exception;
    }

    public function finish()
    {
        $failedTests = $this->failures;
        $vars = $this->templateVars;

        ob_start();

        include_once $this->templateFile;
        $reportContent = ob_get_contents();
        ob_clean();

        file_put_contents($this->logFile, $reportContent);
    }
}