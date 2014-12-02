<?php

namespace Codeception\Module;

class ImageDeviationException extends \PHPUnit_Framework_ExpectationFailedException
{
    private $result;
    private $storage;

    public function __construct($message, \ComparisonResult $comparisonResult, \Storage $storage)
    {
        $this->result = $comparisonResult;
        $this->storage = $storage;

        parent::__construct($message);
    }

    public function getComparisonResult()
    {
        return $this->result;
    }

    public function getStorage()
    {
        return $this->storage;
    }
}