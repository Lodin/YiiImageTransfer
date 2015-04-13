<?php

namespace YiiImageTransfer;

use FileTransfer\GottenFile;

class ImageFile extends GottenFile
{
    protected $_width;
    protected $_height;
    
    protected $_core;

    public function __construct(Transfer $transfer, $url = '')
    {
        $this->_core = Yii::app()->imageTransfer;
        
        parent::__construct($transfer, $url);

        list(
            $this->_width,
            $this->_height
        ) = getimagesize(Yii::getPathOfAlias('webroot').$this->_url);
    }
    
    public function __get($name)
    {
        switch($name) {
            case 'width': 
                return $this->_width;
            case 'height':
                return $this->_height;
            default:
                return parent::__get($name);
        }
    }

    public function setSize($size)
    {
        $sizes = null;

        if (is_string($size)) {
            $sizes = $this->getDefinedSize($size);
        } elseif (is_array($size)) {
            $sizes = $this->getManualSize($size);
        } else {
            throw new YiiITException('Attribute `size` has unrecognized type');
        }

        $coefficient = $sizes['width'] / $sizes['height'];

        if ($sizes['width'] > $sizes->sizeWidth) {
            $sizes['width'] = $sizes->sizeWidth;
            $sizes['height'] = $sizes['width'] / $coefficient;
        }

        if ($sizes['height'] > $sizes->sizeHeight) {
            $sizes['height'] = $sizes->sizeHeight;
            $sizes['width'] = $sizes['height'] * $coefficient;
        }

        $this->_width = $sizes['width'];
        $this->_height = $sizes['height'];
    }

    protected function getDefinedSize($size)
    {
        if (!in_array($size, $this->_core->allowedSizes())) {
            throw new YiiITException('Size can be only `'
                    .implode('`, `', $this->_core->allowedSizes())
                    ."`, not `$size`");
        }

        $sizes = array(
            'width' => 0,
            'height' => 0,
            'sizeWidth' => 0,
            'sizeHeight' => 0,
         );

        $sizes['width'] = $this->_width;
        $sizes['height'] = $this->_height;

        $sizes['sizeWidth'] = $this->_core->sizes[$size]['width'];
        $sizes['sizeHeight'] = $this->_core->sizes[$size]['height'];

        return $sizes;
    }

    protected function getManualSize(array $size)
    {
        if (!isset($size['width'])
            || !isset($size['height'])
            || !count($size) > 2) {
            throw new YiiITException('Parameter `size` should be an array("width"'
                .' => int, "height" => int)');
        }

        $sizes = array(
            'width' => 0,
            'height' => 0,
            'sizeWidth' => 0,
            'sizeHeight' => 0,
         );

        $sizes['width'] = $this->_width;
        $sizes['height'] = $this->_height;

        $sizes['sizeWidth'] = $size['width'];
        $sizes['sizeHeight'] = $size['height'];

        return $sizes;
    }
}
