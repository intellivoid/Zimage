<?php

    require('ppm');

    import('net.intellivoid.zimage');


    $tmp_dir = __DIR__ . DIRECTORY_SEPARATOR . 'extracted_images';
    if(file_exists($tmp_dir) == false)
        mkdir($tmp_dir);

    $zimage = \Zimage\Zimage::load(__DIR__ . DIRECTORY_SEPARATOR . 'png_image.zimage', true);

    foreach($zimage->getImages() as $image)
    {
        $output = $tmp_dir . DIRECTORY_SEPARATOR . 'png_image_' . (string)$image->getSize() . '.jpg';
        if(file_exists($output))
            unlink($output);

        $image->save($output);
    }

    $zimage = \Zimage\Zimage::load(__DIR__ . DIRECTORY_SEPARATOR . 'jpg_image.zimage', true);

    foreach($zimage->getImages() as $image)
    {
        $output = $tmp_dir . DIRECTORY_SEPARATOR . 'jpg_image_' . (string)$image->getSize() . '.jpg';
        if(file_exists($output))
            unlink($output);

        $image->save($output);
    }