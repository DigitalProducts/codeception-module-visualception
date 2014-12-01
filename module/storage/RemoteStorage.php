<?php

class RemoteStorage implements \Storage {

    private $storageServer;

    public function __construct($config)
    {
        $this->userId = $config["userId"];
        $this->storageServer = $config["expectedImageServer"];
    }

    private function getStorageFile($identifier)
    {
        return $this->storageServer . '?userId=' . $this->userId . '&imageId=' . $identifier . ".png";
    }

    public function getImage($identifier) {
        // @todo use curl
        $imageFile = $this->getStorageFile($identifier);
        $imageContent = file_get_contents($imageFile);

        $image = new \Imagick();
        $image->readimageblob($imageContent);

        return $image;
    }
}