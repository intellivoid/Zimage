<?php

    namespace Zimage\Objects;

    use Zimage\Classes\Converter;
    use Zimage\Exceptions\FileNotFoundException;
    use Zimage\Exceptions\UnsupportedImageTypeException;

    class Image
    {
        /**
         * The size of the image
         *
         * @var Size
         */
        private Size $Size;

        /**
         * The SHA256 Checksum of the image
         *
         * @var string
         */
        private string $Hash;

        /**
         * The data of the image
         *
         * @var string
         */
        private string $Data;

        /**
         * Constructs object from a file, automatically converts
         *
         * @param string $input
         * @param bool $from_file
         * @param int $quality
         * @return Image
         * @throws FileNotFoundException
         * @throws UnsupportedImageTypeException
         */
        public static function load(string $input, bool $from_file, int $quality=100): Image
        {
            $jpg_image = Converter::convertImageToJpeg($input, $from_file, $quality);
            $image_size_resource = @getimagesizefromstring($jpg_image);

            if($image_size_resource == false)
                throw new UnsupportedImageTypeException('There was an error while trying to get the image size');

            $image_object = new Image();
            $image_object->Size = new Size();
            $image_object->Size->setWidth((int)$image_size_resource[0]);
            $image_object->Size->setHeight((int)$image_size_resource[1]);
            $image_object->Hash = hash('sha256', $jpg_image);
            $image_object->Data = $jpg_image;

            return $image_object;
        }

        /**
         * Saves image as a jpeg image to disk
         *
         * @param string $file_path
         */
        public function save(string $file_path)
        {
            file_put_contents($file_path, $this->Data);
        }

        /**
         * @return Size
         */
        public function getSize(): Size
        {
            return $this->Size;
        }

        /**
         * @return string
         */
        public function getHash(): string
        {
            return $this->Hash;
        }

        /**
         * @return string
         */
        public function getData(): string
        {
            return $this->Data;
        }

        /**
         * Returns an array representation of the image
         *
         * @param bool $use_compression
         * @param int $compression_level
         * @return array
         */
        public function toArray(bool $use_compression=false, int $compression_level=9): array
        {
            return [
                0x001 => (string)$this->Size,
                0x002 => $this->Hash,
                0x003 => $use_compression,
                0x004 => ($use_compression ? gzcompress($this->Data, $compression_level) : $this->Data)
            ];
        }

        /**
         * Constructs object from an array
         *
         * @param array $data
         * @return Image
         */
        public static function fromArray(array $data): Image
        {
            $image_object = new Image();

            $use_compression = $data[0x003];

            $image_object->Size = new Size($data[0x001]);
            $image_object->Hash = $data[0x002];
            $image_object->Data = ($use_compression ? gzuncompress($data[0x004]) : $data[0x004]);

            return $image_object;
        }
    }