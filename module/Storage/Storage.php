<?php

interface Storage {
    public function hasImage($identifiert);
    public function getImage($identifier);
    public function setImage(\Imagick $image, $identifier);
}