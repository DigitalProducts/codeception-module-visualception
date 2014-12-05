<?php

namespace Codeception\Module;

class ImageDeviationException extends \PHPUnit_Framework_ExpectationFailedException
{
    private $result;
    private $storage;
    private $identifier;

    public function __construct($message, \ComparisonResult $comparisonResult, \Storage $storage, $identifier = "leer")
    {
        $this->result = $comparisonResult;
        $this->storage = $storage;
        $this->identifier = $identifier;

        parent::__construct($message);
    }

    /**
     * @return \ComparisonResult
     */
    public function getComparisonResult()
    {
        return $this->result;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }
}