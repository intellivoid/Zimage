<?php

    namespace Zimage\Classes;


    use TmpFile\TmpFile;
    use Zimage\Exceptions\FileNotFoundException;
    use Zimage\Exceptions\UnsupportedImageTypeException;
    use Zimage\Objects\Image;
    use Zimage\Objects\Size;

    class Converter
    {
        /**
         * Converts a png file to a jpeg, this takes the transparent components and turns it white
         *
         * @param string $input
         * @param bool $from_file
         * @param int $quality
         * @return string
         * @throws FileNotFoundException
         */
        public static function convertPngToJpeg(string $input, bool $from_file=true, int $quality=100): string
        {
            if($from_file && file_exists($input) == false)
                throw new FileNotFoundException('The given path "' . $input . '" does not exist');

            if($from_file == false)
            {
                $temporary_input_file = new TmpFile($input, '.zimage_tmp');
                $input = $temporary_input_file->getFileName();
            }

            // Create an image from a png file
            $image = imagecreatefrompng($input);
            $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
            imagealphablending($bg, TRUE);
            imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagedestroy($image);

            $temporary_file = new TmpFile(null, '.zimage_tmp');
            imagejpeg($bg, $temporary_file->getFileName(), $quality);
            imagedestroy($bg);

            return file_get_contents($temporary_file->getFileName());
        }

        /**
         * The go-to method to convert supported image files to a jpeg
         *
         * @param string $input
         * @param bool $from_file
         * @param int $quality
         * @return string
         * @throws FileNotFoundException
         * @throws UnsupportedImageTypeException
         */
        public static function convertImageToJpeg(string $input, bool $from_file=true, int $quality=100): string
        {
            if($from_file && file_exists($input) == false)
                throw new FileNotFoundException('The given path "' . $input . '" does not exist');

            if($from_file == false)
            {
                $temporary_input_file = new TmpFile($input, '.zimage_tmp');
                $input = $temporary_input_file->getFileName();
            }

            $image_size = @getimagesize($input);
            if($image_size == false)
                throw new UnsupportedImageTypeException('The given file "' . $input . '" is not supported');

            $image_tmp = null;
            switch($image_size[2])
            {
                case IMAGETYPE_BMP:
                    if((imagetypes() & IMAGETYPE_BMP) == false)
                        throw new UnsupportedImageTypeException('BMP File types are not supported on this system');
                    $image_tmp = imagecreatefrombmp($input);
                    break;

                case IMAGETYPE_PNG:
                    if((imagetypes() & IMAGETYPE_PNG) == false)
                        throw new UnsupportedImageTypeException('PNG File types are not supported on this system');

                    // Use dedicated function to convert PNG files to JPEG
                    return self::convertPngToJpeg($input, true, $quality);

                case IMAGETYPE_JPEG:
                    if((imagetypes() & IMAGETYPE_JPEG) == false)
                        throw new UnsupportedImageTypeException('JPEG File types are not supported on this system');
                    $image_tmp = imagecreatefromjpeg($input);
                    break;

                case IMAGETYPE_GIF:
                    if((imagetypes() & IMAGETYPE_GIF) == false)
                        throw new UnsupportedImageTypeException('GIF File types are not supported on this system');
                    $image_tmp = imagecreatefromgif($input);
                    break;

                case IMAGETYPE_WEBP:
                    if((imagetypes() & IMAGETYPE_WEBP) == false)
                        throw new UnsupportedImageTypeException('WEBP File types are not supported on this system');
                    $image_tmp = imagecreatefromwebp($input);
                    break;

                case IMAGETYPE_XBM:
                    if((imagetypes() & IMAGETYPE_XBM) == false)
                        throw new UnsupportedImageTypeException('XBM File types are not supported on this system');
                    $image_tmp = imagecreatefromxbm($input);
                    break;

                default:
                    throw new UnsupportedImageTypeException('The given file type "' . $image_size[2] . '" from "' . $input . '" cannot be converted to a jpeg file');
            }

            $temporary_file = new TmpFile(null, '.zimage_tmp');
            imagejpeg($image_tmp,  $temporary_file->getFileName(), $quality);
            imagedestroy($image_tmp);

            return file_get_contents($temporary_file->getFileName());
        }

        /**
         * Resizes an image and keeps the aspect ratio
         *
         * @param Image $image
         * @param int $width
         * @param int $quality
         * @return Image
         * @throws FileNotFoundException
         * @throws UnsupportedImageTypeException
         */
        public static function resizeAspectImage(Image $image, int $width, int $quality=100): Image
        {
            list($width, $height) = getimagesizefromstring($image->getData());
            $new_width = 1200;
            $new_height = ceil($height * ($new_width/$width));

            $image_p = imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefromstring($image->getData());
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

            $temporary_input_file = new TmpFile(null, '.zimage_tmp');
            imagejpeg($image_p, $temporary_input_file->getFileName(), $quality);
            imagedestroy($image_p);

            self::removeExif($temporary_input_file->getFileName());
            return Image::load($temporary_input_file->getFileName(), true, $quality);
        }

        /**
         * Resizes an image and keeps the aspect ratio
         *
         * @param Image $image
         * @param Size $size
         * @param int $quality
         * @return Image
         * @throws FileNotFoundException
         * @throws UnsupportedImageTypeException
         */
        public static function resizeImage(Image $image, Size $size, int $quality=100): Image
        {
            list($width, $height) = getimagesizefromstring($image->getData());

            $image_p = imagecreatetruecolor($size->getWidth(), $size->getHeight());
            $image = imagecreatefromstring($image->getData());
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $size->getWidth(), $size->getHeight(), $width, $height);

            $temporary_input_file = new TmpFile(null, '.zimage_tmp');
            imagejpeg($image_p, $temporary_input_file->getFileName(), $quality);
            imagedestroy($image_p);

            self::removeExif($temporary_input_file->getFileName());
            return Image::load($temporary_input_file->getFileName(), true, $quality);
        }

        /**
         * Removes exif data from a jpeg file
         *
         * @param $file_path
         * @return TmpFile
         */
        public static function removeExif($file_path): string
        {
            // Open the input file for binary reading
            $f1 = fopen($file_path, 'rb');
            // Open the output file for binary writing
            $f2 = fopen($file_path . '.tmp', 'wb');

            // Find EXIF marker
            while (($s = fread($f1, 2)))
            {
                $word = @unpack('ni', $s)['i'];
                if ($word == 0xFFE1)
                {
                    // Read length (includes the word used for the length)
                    $s = fread($f1, 2);
                    $len = unpack('ni', $s)['i'];
                    // Skip the EXIF info
                    fread($f1, $len - 2);
                    break;
                }
                else
                {
                    fwrite($f2, $s, 2);
                }
            }

            // Write the rest of the file
            while (($s = fread($f1, 4096)))
            {
                fwrite($f2, $s, strlen($s));
            }

            fclose($f1);
            fclose($f2);

            unlink($file_path);
            rename($file_path . '.tmp', $file_path);

            return $file_path;
        }

    }