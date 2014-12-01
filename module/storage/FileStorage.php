<?php

class FileStorage {

    private $storageDir;

    public function __construct($config)
    {
        // @todo error handling if current image dir is not set
        $this->storageDir = $config["expectedImageDir"];
    }

    private function getStorageFile($identifier)
    {
        return $this->storageDir . DIRECTORY_SEPARATOR . $identifier . ".png";
    }

    public function getImage($identifier) {

        return new \Imagick($this->getStorageFile($identifier));
    }
}