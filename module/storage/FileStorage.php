<?php

class FileStorage implements \Storage {

    private $storageDir;


    /**
     * Constructor is setting up the path for the images
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (array_key_exists('expectedImageDir', $config)) {
            $this->storageDir = $config["expectedImageDir"];
        } else {
            $this->storageDir = \Codeception\Configuration::dataDir() . 'VisualCeption/expected/';
        }
    }

    /**
     * Returns the filename of the images connected to the identifier
     *
     * @param string $identifier the validation identifier
     * @return string the image file
     */
    private function getStorageFile($identifier)
    {
        return $this->storageDir . $identifier . ".png";
    }

    /**
     * Returns the image (Imagick) connected to the identifier
     *
     * @param string $identifier the validation identifier
     * @return Imagick
     */
    public function getImage($identifier)
    {
        $imageFile = $this->getStorageFile($identifier);
        if( !file_exists($imageFile)) {
            $image = new \Imagick();
            $image->newImage(1, 1, new ImagickPixel('white'));
            return $image;
        }
        return new \Imagick($imageFile);
    }
}