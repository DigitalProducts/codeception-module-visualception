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

    public function setImage(\Imagick $image, $identifier)
    {
        $url = $this->getStorageFile($identifier);

        $ch = curl_init();

        $imageContent = base64_encode($image->getimageblob());

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "image=" . $imageContent);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);

        var_dump($server_output);

        curl_close ($ch);
    }
}