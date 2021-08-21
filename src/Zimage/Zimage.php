<?php

    namespace Zimage;

    use Zimage\Objects\Size;

    class Zimage
    {
        /**
         * The original size of the image
         *
         * @var Size
         */
        private $original_size;

        /**
         * The sizes available of this image
         *
         * @var Size[]
         */
        private $sizes;

        private $images;

        public function __construct()
        {

        }
    }