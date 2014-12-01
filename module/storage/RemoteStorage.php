<?php

class RemoteStorage {

    private $storageServer;

    public function __construct($config)
    {
        $this->userId = $config["userId"];
        $this->storageServer = $config["expectedImageServer"];
    }

    private function getStorageFile($identifier)
    {
        return $this->storageServer . '/' . $this->userId . '/' . $identifier . ".png";
    }

    public function getImage($identifier) {
        $image = file_get_contents($this->getStorageFile($identifier));
        return new \Imagick($image);
    }
}