<?php

    namespace Zimage\Objects;

    class Size
    {
        /**
         * The height of the size
         *
         * @var int
         */
        private $Height;

        /**
         * The width of the size
         *
         * @var int
         */
        private $Width;

        /**
         * @return string
         */
        public function __toString()
        {
            return $this->Width . ", " . $this->Height;
        }

        /**
         * @return int
         */
        public function getHeight(): int
        {
            return $this->Height;
        }

        /**
         * @param int $Height
         */
        public function setHeight(int $Height)
        {
            $this->Height = $Height;
        }

        /**
         * @return int
         */
        public function getWidth(): int
        {
            return $this->Width;
        }

        /**
         * @param int $Width
         */
        public function setWidth(int $Width)
        {
            $this->Width = $Width;
        }
    }