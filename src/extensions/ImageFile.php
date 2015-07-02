<?php

namespace YiiImageTransfer;

use YiiImageTransfer\YiiImageTransfer;
use FileTransfer\Wrappers\GottenFile;

class ImageFile extends GottenFile
{
    protected $_width;
    protected $_height;

    protected $_core;

    public function __construct(Transfer $transfer, $url = '')
    {
        parent::__construct($transfer, $url);

        list(
            $this->_width,
            $this->_height
        ) = getimagesize(Yii::getPathOfAlias('webroot').$this->_url);
    }
    
    /**
     * Links ImageFile with main transfer plugin
     * 
     * @param YiiImageTransfer $core
     */
    public function bind(YiiImageTransfer $core)
    {
        $this->_core = $core;
    }
    
    public function __get($name)
    {
        switch ($name) {
            case 'width':
                return $this->_width;
            case 'height':
                return $this->_height;
            default:
                return parent::__get($name);
        }
    }

    /**
     * Recalculates original image size due to user defined resolution. File
     * will not be croped but resized by html tools to new resolution. This
     * method can be used to fast resize with keeping proportions without
     * creating new file.
     * 
     * @param array|string $size new size of image. Can be defined by preset
     *                           size names or by manual width-height values
     * @throws YiiITException if size is not array or string
     */
    public function setSize($size)
    {
        if (is_string($size)) {
            $sizes = $this->getDefinedSize($size);
        } elseif (is_array($size)) {
            $sizes = $this->getManualSize($size);
        } else {
            throw new YiiITException('Attribute `size` has unrecognized type');
        }
        
        $coefficient = $sizes['width'] / $sizes['height'];

        if ($sizes['sizeWidth'] !== null && $sizes['width'] > $sizes['sizeWidth']) {
            $sizes['width'] = $sizes['sizeWidth'];
            $sizes['height'] = $sizes['width'] / $coefficient;
        }

        if ($sizes['sizeHeight'] !== null && $sizes['height'] > $sizes['sizeHeight']) {
            $sizes['height'] = $sizes['sizeHeight'];
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

        return array(
            'width' => $this->_width,
            'height' => $this->_height,
            'sizeWidth' => $this->_core->sizes[$size]['width'],
            'sizeHeight' => $this->_core->sizes[$size]['height'],
        );
    }

    protected function getManualSize(array $size)
    {
        if (!isset($size['width'])
            || !isset($size['height'])
            || !count($size) > 2) {
            throw new YiiITException('Parameter `size` should be an array("width"'
                .' => int, "height" => int)');
        }

        return array(
            'width' => $this->_width,
            'height' => $this->_height,
            'sizeWidth' => $size['width'],
            'sizeHeight' => $size['height'],
         );
    }
}
