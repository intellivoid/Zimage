<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace Zimage;

    use PpmZiProto\ZiProto;
    use Zimage\Classes\Converter;
    use Zimage\Exceptions\CannotGetOriginalImageException;
    use Zimage\Exceptions\CannotRemoveOriginalImageSizeException;
    use Zimage\Exceptions\FileNotFoundException;
    use Zimage\Exceptions\InvalidZimageFileException;
    use Zimage\Exceptions\SizeNotSetException;
    use Zimage\Objects\Image;
    use Zimage\Objects\Size;

    class Zimage
    {
        /**
         * The original size of the image
         *
         * @var Size|null
         */
        private $original_size;

        /**
         * @var Image[]
         */
        private $images;

        /**
         * @var bool
         */
        private $use_compression;

        /**
         * @var int
         */
        private $compression_level;

        /**
         * Public Constructor
         */
        public function __construct()
        {
            $this->original_size = null;
            $this->use_compression = false;
            $this->compression_level = 9;
            $this->images = [];
        }

        /**
         * @param string $input
         * @param bool $from_file
         * @param int $quality
         * @return Zimage
         * @throws Exceptions\FileNotFoundException
         * @throws Exceptions\UnsupportedImageTypeException
         */
        public static function createFromImage(string $input, bool $from_file, int $quality=100): Zimage
        {
            $zimage_object = new Zimage();

            $image_object = Image::load($input, $from_file, $quality);
            $zimage_object->images[] = $image_object;
            $zimage_object->original_size = $image_object->getSize();

            return $zimage_object;
        }

        /**
         * @return Image[]
         */
        public function getImages(): array
        {
            return $this->images;
        }

        /**
         * @return Size|null
         */
        public function getOriginalSize(): ?Size
        {
            return $this->original_size;
        }

        /**
         * Returns the original image
         *
         * @return Image
         * @throws CannotGetOriginalImageException
         */
        public function getOriginalImage(): Image
        {
            foreach($this->images as $image)
            {
                if((string)$image->getSize() == (string)$this->original_size)
                    return $image;
            }

            throw new CannotGetOriginalImageException('The original image cannot be retrieved');
        }

        /**
         * Returns an existing image by size
         *
         * @param Size $size
         * @param bool $set_size
         * @param int $quality
         * @return Image
         * @throws CannotGetOriginalImageException
         * @throws Exceptions\FileNotFoundException
         * @throws Exceptions\UnsupportedImageTypeException
         * @throws SizeNotSetException
         */
        public function getImageBySize(Size $size, bool $set_size=false, bool $keep_aspect=false, int $quality=100): Image
        {
            foreach($this->images as $image)
            {
                if((string)$image->getSize() == (string)$size)
                    return $image;
            }

            if($set_size)
                return $this->setSize($size, $keep_aspect, $quality);

            throw new SizeNotSetException('The requested image size is not set');
        }

        /**
         * Returns an array of available sizes
         *
         * @return Size[]|string[]
         */
        public function getSizes(bool $as_string=false): array
        {
            $sizes = [];
            foreach($this->images as $image)
                $sizes[] = ($as_string ? (string)$image->getSize() : $image->getSize());

            return $sizes;
        }

        /**
         * Adds a new size to the file format
         *
         * @param Size $size
         * @param int $quality
         * @param bool $keep_aspect
         * @return Image
         * @throws CannotGetOriginalImageException
         * @throws Exceptions\UnsupportedImageTypeException
         * @throws FileNotFoundException
         * @throws SizeNotSetException
         */
        public function setSize(Size $size, bool $keep_aspect=false, int $quality=100): Image
        {
            if(in_array((string)$size, $this->getSizes(true)))
                /** @noinspection PhpUnhandledExceptionInspection */
                return $this->getImageBySize($size);

            if($keep_aspect)
            {
                $resized_image = Converter::resizeAspectImage($this->getOriginalImage(), $size->getWidth(), $quality);
            }
            else
            {
                $resized_image = Converter::resizeImage($this->getOriginalImage(), $size, $quality);
            }

            if(in_array((string)$resized_image->getSize(), $this->getSizes(true)))
                /** @noinspection PhpUnhandledExceptionInspection */
                return $this->getImageBySize($resized_image->getSize());

            $this->images[] = $resized_image;

            return $resized_image;
        }

        /**
         * Sets multiple sizes
         *
         * @param array $sizes
         * @param int $quality
         * @param bool $keep_aspect
         * @return Size[]
         * @throws CannotGetOriginalImageException
         * @throws Exceptions\UnsupportedImageTypeException
         * @throws FileNotFoundException
         * @throws SizeNotSetException
         */
        public function setSizes(array $sizes, bool $keep_aspect=false, int $quality=100): array
        {
            $results = [];
            foreach($sizes as $size)
                $results[] = $this->setSize($size, $keep_aspect, $quality);

            return $results;
        }

        /**
         * Removes an existing size from the image
         *
         * @param Size $size
         * @return void
         * @throws CannotRemoveOriginalImageSizeException
         */
        public function removeSize(Size $size)
        {
            if((string)$size == (string)$this->original_size)
                throw new CannotRemoveOriginalImageSizeException('The original image size cannot be removed');

            $reconstructed_array = [];

            foreach($this->images as $image)
            {
                if((string)$image->getSize() == (string)$size)
                    continue;

                $reconstructed_array[] = $image;
            }

            $this->images = $reconstructed_array;
        }

        /**
         * Returns the set of images as a array representation
         *
         * @return array
         */
        public function imagesToArray(): array
        {
            $images = [];

            foreach($this->images as $image)
                $images[] = $image->toArray($this->use_compression, $this->compression_level);

            return $images;
        }

        /**
         * @return string
         */
        public function __toString()
        {
            return ZiProto::encode([
                0x001 => 'zimage',
                0x002 => $this->use_compression,
                0x003 => $this->compression_level,
                0x004 => (string)$this->original_size,
                0x005 => $this->imagesToArray()
            ]);
        }

        /**
         * Saves the Zimage image file to disk
         *
         * @param string $file_path
         */
        public function save(string $file_path)
        {
            file_put_contents($file_path, (string)$this);
        }

        /**
         * Parses a Zimage file format and returns the constructed object
         *
         * @param string $input
         * @param bool $as_file
         * @return Zimage
         * @throws FileNotFoundException
         * @throws InvalidZimageFileException
         */
        public static function load(string $input, bool $as_file=true): Zimage
        {
            if($as_file && file_exists($input) == false)
                throw new FileNotFoundException('The file path "' . $input . '" was not found.');

            if($as_file && file_exists($input))
                $input = file_get_contents($input);

            $decoded_data = ZiProto::decode($input);
            if(isset($decoded_data[0x001]) == false || $decoded_data[0x001] !== 'zimage')
                throw new InvalidZimageFileException('The given input is not a valid Zimage file format.');

            $zimage_object = new Zimage();
            $zimage_object->original_size = new Size($decoded_data[0x004]);
            $zimage_object->images = [];

            foreach($decoded_data[0x005] as $datum)
                $zimage_object->images[] = Image::fromArray($datum);

            return $zimage_object;
        }

        /**
         * @return bool
         */
        public function isUsingCompression(): bool
        {
            return $this->use_compression;
        }

        /**
         * @param bool $use_compression
         */
        public function setUseCompression(bool $use_compression): void
        {
            $this->use_compression = $use_compression;
        }

        /**
         * @return int
         */
        public function getCompressionLevel(): int
        {
            return $this->compression_level;
        }

        /**
         * @param int $compression_level
         */
        public function setCompressionLevel(int $compression_level): void
        {
            $this->compression_level = $compression_level;
        }

    }