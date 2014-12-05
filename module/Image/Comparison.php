<?php

class Comparison
{
    /**
     * This function compared two images
     *
     * @param Imagick $image1
     * @param Imagick $image2
     * @return ComparisonResult
     */
    public function compare(\Imagick $image1, \Imagick $image2)
    {
        $imagick1Size = $image1->getImageGeometry();
        $imagick2Size = $image2->getImageGeometry();

        $maxWidth = max($imagick1Size['width'], $imagick2Size['width']);
        $maxHeight = max($imagick1Size['height'], $imagick2Size['height']);

        $image1->extentImage($maxWidth, $maxHeight, 0, 0);
        $image2->extentImage($maxWidth, $maxHeight, 0, 0);

        $result = $image1->compareImages($image2, \Imagick::METRIC_MEANSQUAREERROR);
        $result[0]->setImageFormat('png');

        return new \ComparisonResult(round($result[1] * 100, 2), $image1, $image2, $result[0]);
    }
}