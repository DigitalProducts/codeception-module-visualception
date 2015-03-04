<?php
/**
 *
 */

namespace Codeception\Module;

class VisualCeptionReporter extends \Codeception\Module
{
    private $reporter;

    public function __construct($config)
    {
        $result = parent::__construct($config);
        $this->init();
        return $result;
    }

    private function init()
    {
        $this->debug("Initializing VisualCeptionReport");

        if (array_key_exists('reporter', $this->config)) {
            $this->setReporter($this->config["reporter"]);
        } else {
            $this->setReporter(array('class' => 'NullReporter'), null);
        }
    }

    private function setReporter($config)
    {
        $reporterClass = $config['class'];
        $this->reporter = new $reporterClass($config);

        $this->debug('Reporter: ' . get_class($this->reporter));
    }

    public function _beforeSuite($settings = array())
    {
        if (!$this->hasModule("VisualCeption")) {
            throw new \Exception("VisualCeptionReporter uses VisualCeption. Please be sure that this module is activated.");
        }
    }

    public function _afterSuite()
    {
        $this->reporter->finish();
    }

    public function _failed(\Codeception\TestCase $test, $fail)
    {
        if ($fail instanceof ImageDeviationException) {
            $this->reporter->processFailure($fail);
        }
    }
}