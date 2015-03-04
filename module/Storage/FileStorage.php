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

        if( !is_dir($this->storageDir) ) {
            mkdir( $this->storageDir, 0777, true);
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
        return new \Imagick($imageFile);
    }

    public function setImage(\Imagick $image, $identifier)
    {
        $filename = $this->getStorageFile($identifier);
        return $image->writeImage($filename);
    }

    public function hasImage($identifier) {
        return file_exists($this->getStorageFile($identifier));
    }
}