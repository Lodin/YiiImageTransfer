<?php

namespace YiiImageTransfer;

/**
 * Creates an image section using gotten image code uploaded earlier. HTML-code
 * will look like next: 
 * 
 * <{tag} class="imgtr-wrapper {htmlOptions['class']}" {htmlOptions}>
 *     <img src="{code}" {imageOptions} />
 * </{tag}>
 */
class ImageGetter extends CWidget
{
    /**
     * Code of image uploaded earlier
     * @var string
     */
    public $code = '';
    
    /**
     * Directory under the main image directory separating image by different
     * types
     * @var string 
     */
    public $subdir = '';
    
    /**
     * Predefined image size name
     * @var type 
     */
    public $size = '';
    
    /**
     * Defines tag which can be set to wrapper. By default it is `div`, but
     * can be set e.g. to `li`, if the list is need.
     * @var string 
     */
    public $tag = 'div';
    
    /**
     * Image parent div wrapper html options
     * @var array
     */
    public $htmlOptions = array();
    
    /**
     * Image tag html options. Can include every property except `src`
     * @var array 
     */
    public $imageOptions = array();
    
    /**
     * User defined alias for YiiImageTransfer plugin. Default is
     * `imageTransfer`
     * @var string 
     */
    public $alias = 'imageTransfer';
    
    protected $_core; 
    
    public function init()
    {
        $this->_core = Yii::app()->{$this->alias};
        
        if(!in_array($this->size, $this->_core->allowedSizes())) {
            throw new YiiITException('Size can be only `'
                .implode('`, `', $this->_core->allowedSizes())
                ."`, not `$this->size`");
        }
        
        Yii::app()->getClientScript()
            ->registerCssFile($this->_core->assetUrl.'/css/imagetransfer.css');
    }
    
    public function run()
    {
        $sizes = $this->_core->sizes[$this->size];
        $img = $this->_core->get($this->code, $this->subdir, $this->size);
        $img->bind($this->_core);
        
        if($this->size !== '') {
            $img->setSize($this->size);
        } else {
            $img->setSize(array(
                'width' => !empty($this->imageOptions['width']) ?
                    $this->imageOptions['width']
                    : null,
                'height' => !empty($this->imageOptions['height']) ?
                    $this->imageOptions['height']
                    : null,
            ));
        }
    }
}