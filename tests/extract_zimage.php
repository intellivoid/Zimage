<?php

    require('ppm');

    import('net.intellivoid.zimage');

    $zimage = \Zimage\Zimage::createFromImage(__DIR__ . DIRECTORY_SEPARATOR . 'png_image.png', true);
    $zimage->setSizes([
        new \Zimage\Objects\Size("32x32"),
        new \Zimage\Objects\Size("64x64"),
        new \Zimage\Objects\Size("126x126"),
        new \Zimage\Objects\Size("256x256"),
    ], true);

    $zimage->save(__DIR__ . DIRECTORY_SEPARATOR . 'png_image.zimage');