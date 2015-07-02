<?php

use FileTransfer\Transfer;
use abeautifulsite\SimpleImage;

/**
 * Wrapper over FileTransfer base class for Yii.
 */
class YiiImageTransfer extends CApplicationComponent
{
    /**
     * Name of default folder for uploading files.
     *
     * @var string
     */
    public $dir;

    /**
     * File extensions allowed to upload.
     *
     * @var array
     */
    public $allowedExtensions = array();

    /**
     * List of image sizes with defined width and height.
     *
     * @var array
     */
    public $sizes = array();

    /**
     * Limit of file uploading in single folder. If count grows up, system
     * creates new folder.
     *
     * @var int
     */
    public $filesInFolder = 50;

    /**
     * Image to show if requested image does not exist.
     *
     * @var string|null
     */
    public $placeholder = null;

    /**
     * Url of assets published by Yii.
     *
     * @var string
     */
    protected $assetUrl = '';

    protected $_transfer;

    /**
     * Initializes component, checks input data and register component assets.
     *
     * @throws YiiITException on input data checking error
     */
    public function init()
    {
        Yii::setPathOfAlias('imgtr', __DIR__.'/../');
        require Yii::getPathOfAlias('imgtr.extensions.ImageFile').'.php';

        $this->initAssets();
        $this->checkData();

        $placeholder = $this->placeholder !== null ?
            $this->placeholder :
            $this->assetUrl.'/images/placeholder.png';

        $this->_transfer = new Transfer(array(
            'absolutePath' => Yii::getPathOfAlias('webroot'),
            'relativePath' => Yii::app()->baseUrl,
            'dir' => $this->dir,
            'allowedExtensions' => $this->allowedExtensions,
            'handlers' => $this->createHandlers(),
            'filesInFolder' => $this->filesInFolder,
            'mimeCacheFile' => Yii::getPathOfAlias('webroot').'/protected/data/mimetypes.php',
            'emptyFileReplacement' => $placeholder,
            'gottenFileClass' => 'ImageFile',
        ));
    }

    /**
     * Uploads all allowed files from $_FILES; creates `$subdir` folder in the
     * target folder; then puts all files into the numbered folders by
     * `$filesInFolder` in each.
     *
     * @param array  $files     $_FILES array
     * @param string $subdir    subdirectory to separate images of different types
     * @param array  $userSizes predefined user sizes
     */
    public function upload($files, $subdir, $userSizes)
    {
        return $this->_transfer->upload($files, $subdir, $userSizes);
    }

    /**
     * Finds and returns file in `$subdir` folder by its code created at
     * uploading.
     *
     * @param string $code   code of image created at uploading
     * @param string $subdir name of subdirectory to separate different image
     *                       types
     * @param string $size   image size name (one of listed in `$sizes` field)
     *
     * @return ImageFile result file
     */
    public function get($code, $subdir, $size)
    {
        return $this->_transfer->get($code, $subdir, $size);
    }

    /**
     * Returns list of size names.
     *
     * @return array
     */
    public function allowedSizes()
    {
        return array_keys($this->sizes);
    }

    public function __get($name)
    {
        switch ($name) {
            case 'assetUrl' :
                return $this->assetUrl;
            default:
                return parent::__get($name);
        }
    }

    /**
     * Removes selected file.
     *
     * @param string $code    file code (filename created at upload
     *                        operation)
     * @param string $subdir  subdirectory in the uploading files directory
     *                        to separate uploading files by user defined
     *                        types
     * @param string $handler name of handler applied to file on uploading
     */
    public function remove($code, $subdir, $handler)
    {
        $this->_transfer->remove($code, $subdir, $handler);
    }

    /**
     * Returns placeholder - an image to replace requested image if it is not
     * found. Returns default plugin placeholder if custom does not set.
     *
     * @return ImageFile
     */
    public function getPlaceholder()
    {
        return new ImageFile($this->_transfer, '');
    }

    /*
     * Publishes assets by Yii asset publishing system
     */
    protected function initAssets()
    {
        $this->assetUrl = substr(Yii::app()->assetManager->publish(
            realpath(__DIR__.'/../assets'),
            false,
            -1,
            YII_DEBUG
        ), 1);
    }

    /*
     * Tests plugin options
     * @throws YiiITException on wrong definition of some option
     */
    protected function checkData()
    {
        if (empty($this->sizes)) {
            throw new YiiITException('At least one image size should be defined');
        }

        foreach ($this->sizes as $size) {
            if (count($size) > 2
            || !isset($size['width'])
            || !isset($size['height'])
            || !is_integer($size['width'])
            || !is_integer($size['height'])) {
                throw new YiiITException('Any size should be an'
                    .' array("width" => int, "height" => int)');
            }
        }
    }

    /*
     * Creates handlers for Transfer object
     * @return array
     */
    protected function createHandlers()
    {
        $result = array();

        foreach ($this->sizes as $name => $size) {
            $result[$name] = function ($file, $fname) use ($size) {
                (new SimpleImage($file->tmpName))
                    ->best_fit($size['width'], $size['height'])
                    ->save($fname);
            };
        }

        return $result;
    }
}
