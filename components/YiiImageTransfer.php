<?php
namespace YiiImageTransfer;

use \FileTransfer\Transfer;
use \abeautifulsite\SimpleImage;

class YiiImageTransfer extends CApplicationComponent
{
    /**
     * Default folder for files
     * @var string
     */
    public $dir;
    
    /**
     * File extensions allowed to upload
     * @var array
     */
    public $allowedExtensions = array();

    public $sizes = array();
     
    /**
     * Maximum quantity of files in one numbered folder
     * @var type 
     */
    public $filesInFolder = 50;

    public $assetUrl = '';

    protected $_transfer;

    /**
     * Initializes component, checks input data and register component assets.
     * 
     * @throws YiiITException on input data checking error
     */
    public function init()
    {
        $this->initAssets();
        $this->checkData();

        $this->_transfer = new Transfer(array(
            'absolutePath' => Yii::getPathOfAlias('webroot'),
            'relativePath' => Yii::app()->basePath,
            'dir' => $this->dir,
            'allowedExtensions' => $this->allowedExtensions,
            'types' => $this->createFTTypes(),
            'filesInFolder' => $this->filesInFolder,
            'mimeCacheFile' => Yii::getPathOfAlias('webroot') . '/protected/data/mimetypes.php',
            'emptyFileReplacement' => $this->placeholder,
            'gottenFileClass' => 'ImageFile'
        ));
    }
    
    /**
     * Uploads all allowed files from $_FILES; creates `$subdir` folder in the
     * target folder (`$images` or `$files`); then puts all files into the
     * numbered folders by $filesInFolder in each.
     * 
     * @param type $files
     * @param type $subdir
     * @param type $needSizes
     */
    public function upload( $files, $subdir, $needSizes)
    {
        $this->_transfer->upload($files, $subdir, $needSizes); 
    }
    
    public function get( $id, $subdir, $size, $isAbsolute = false )
    {
        return $this->_transfer->get($id, $subdir, $size, $isAbsolute);
    }
    
    public function allowedSizes()
    {
        return array_keys( $this->sizes );
    }
    
    public function __get($name) {
        switch($name) {
            case 'placeholder':
                return $this->assetUrl . '/images/image_placeholder.png';
            default:
                return parent::__get($name);
        }
    }

    protected function initAssets()
    {
        $this->assetUrl = Yii::app()->assetManager->publish( 
            realpath(__DIR__ . '../assets'),
            false,
            -1,
            YII_DEBUG
        );
    }

    protected function checkData()
    {
        if( empty( $this->sizes ) )
            throw new YiiITException('At least one image type should be defined');

        foreach( $this->sizes as $size )
        {
            if(
                count( $size ) > 2
                || !isset( $size['width'] )
                || !isset( $size['height'] )
                || !is_integer( $size['width'] )
                || !is_integer( $size['height'] )
            )
            throw new YiiITException( 'Any size should be an'
                .' array("width" => int, "height" => int)' );
        }
      
    }

    protected function createFTTypes()
    {
        $result = array();

        foreach($this->sizes as $name => $size) {
            $result[$name] = function($file, $fname) use($size) {
                (new SimpleImage($file->tmpName))
                    ->best_fit($size['width'], $size['height'])
                    ->save($fname); 
            };
        }

        return $result;
    } 
}
