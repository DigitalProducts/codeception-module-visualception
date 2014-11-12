<?php
/**
 * Created by PhpStorm.
 * User: langn
 * Date: 02.10.14
 * Time: 14:21
 *
 * /citools/tools/common/php/5.5.9/bin/php codecept.phar
 *
 * @todo config logfile
 *
 */

namespace Codeception\Module;

class VisualCeptionReporter extends \Codeception\Module
{
    private $failed = array();
    private $logFile;
    private $templateVars = array();
    private $templateFile;

    private $referenceImageDir;

    public function __construct($config)
    {
        $result = parent::__construct($config);
        $this->init();
        return $result;
    }

    private function init()
    {
        $this->debug("Initializing VisualCeptionReportt");

        $this->logFile = \Codeception\Configuration::logDir() . 'vcresult.html';

        if (array_key_exists('templateVars', $this->config)) {
            $this->templateVars = $this->config["templateVars"];
        }

        if (array_key_exists('templateFile', $this->config)) {
            $this->templateFile = $this->config["templateFile"];
        } else {
            $this->templateFile = __DIR__ . "/report/template.php";
        }
    }

    public function _beforeSuite()
    {
        if (!$this->hasModule("VisualCeption")) {
            throw new \Exception("VisualCeptionReporter uses VisualCeption. Please be sure that this module is activated.");
        }

        $this->referenceImageDir = $this->getModule("VisualCeption")->getReferenceImageDir();

        $this->debug( "VisualCeptionReporter: templateFile = " . $this->templateFile );
    }

    public function _afterSuite()
    {
        $failedTests = $this->failed;
        $vars = $this->templateVars;
        $referenceImageDir = $this->referenceImageDir;
        $i = 0;

        ob_start();
        include_once $this->templateFile;
        $reportContent = ob_get_contents();
        ob_clean();

        $this->debug("Trying to store file (".$this->logFile.")");
        file_put_contents($this->logFile, $reportContent);
    }

    public function _failed(\Codeception\TestCase $test, $fail)
    {
        if ($fail instanceof ImageDeviationException) {
            $this->failed[] = $fail;
        }
    }
}