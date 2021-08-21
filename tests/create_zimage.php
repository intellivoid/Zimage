<?php

    require('ppm');

    import('net.intellivoid.zimage');

    $zimage = \Zimage\Zimage::createFromImage(__DIR__ . DIRECTORY_SEPARATOR . 'png_image.png', true);
    $zimage->setUseCompression(false);
    $zimage->setSizes([
        new \Zimage\Objects\Size("32x32"),
        new \Zimage\Objects\Size("64x64"),
        new \Zimage\Objects\Size("126x126"),
        new \Zimage\Objects\Size("256x256"),
    ], false);
    $zimage->save(__DIR__ . DIRECTORY_SEPARATOR . 'png_image.zimage');


    $zimage = \Zimage\Zimage::createFromImage(__DIR__ . DIRECTORY_SEPARATOR . 'jpg_image.jpg', true);
    $zimage->setUseCompression(false);
    $zimage->setSizes([
        new \Zimage\Objects\Size("32x32"),
        new \Zimage\Objects\Size("64x64"),
        new \Zimage\Objects\Size("126x126"),
        new \Zimage\Objects\Size("256x256"),
    ], false);
    $zimage->save(__DIR__ . DIRECTORY_SEPARATOR . 'jpg_image.zimage');