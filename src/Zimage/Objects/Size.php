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
         * Public Constructor
         *
         * @param string|null $input
         */
        public function __construct(?string $input=null)
        {
            $this->Height = 0;
            $this->Width = 0;

            if($input !== null)
            {
                $exploded = explode('x', $input);
                if(count($exploded) == 2 && is_numeric($exploded[0]) && is_numeric($exploded[1]))
                {
                    $this->Width = $exploded[0];
                    $this->Height = $exploded[1];
                }
            }
        }

        /**
         * @return string
         */
        public function __toString()
        {
            return $this->Width . 'x' . $this->Height;
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