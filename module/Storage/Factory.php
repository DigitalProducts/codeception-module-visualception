<?php

class Factory
{
    /**
     * @param array $config
     * @return Storage
     * @throws RuntimeException
     */
    public static function getStorage(array $config)
    {
        if (array_key_exists('storageStrategy', $config)) {
            switch($config['storageStrategy']) {
                case "RemoteStorage":
                    $storage = new \RemoteStorage($config);
                    break;
                case "FileStorage":
                    $storage = new \FileStorage($config);
                    break;
                default:
                    throw new \RuntimeException('The given storage strategy is not supported.');
            }
        }else{
            $storage = new \FileStorage($config);
        }

        return $storage;
    }
}