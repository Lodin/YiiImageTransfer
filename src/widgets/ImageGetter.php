<?php

namespace YiiImageTransfer;

/**
 * Creates an image section using gotten image code uploaded earlier. HTML-code
 * will look like next: 
 * 
 * <{tag} class="imgtr-wrapper {htmlOptions['class']}" {htmlOptions}>
 *     <img src="{code}" {imageOptions} />
 *     {htmlInjection}
 * </{tag}>
 */
class ImageGetter extends CWidget
{
    /**
     * Code of image uploaded earlier
     * @var string|null
     */
    public $code = null;
    
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
     * Image wrapper tag html options
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
        $this->checkData();
        
        Yii::app()->getClientScript()
            ->registerCssFile($this->_core->assetUrl.'/css/imagetransfer.css');
    }
    
    public function run()
    {
        if($this->code !== null) {
            $img = $this->_core->get($this->code, $this->subdir, $this->size);
        } else {
            $img = $this->_core->getPlaceholder();
        }
        
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
        
        $htmlOptions = isset($this->htmlOptions) ? $this->htmlOptions : array();
        $htmlOptions['class'] = isset($htmlOptions['class']) ?
            $htmlOptions['class'] .= ' imgtrgt-wrapper'
            : $htmlOptions['class'] = 'imgtrgt-wrapper';
        
        $imageOptions = $this->imageOptions;
        unset($imageOptions['width']);
        unset($imageOptions['height']);
        
        echo CHtml::openTag($this->tag, $htmlOptions);
        echo CHtml::tag('img', array_merge(array(
            'src' => $img->url,
            'width' => $img->width,
            'height' => $img->height
        ), $imageOptions));
        echo CHtml::closeTag($this->tag);
    }
    
    protected function checkData()
    {
        if(!in_array($this->size, $this->_core->allowedSizes())) {
            throw new YiiITException('Size can be only `'
                .implode('`, `', $this->_core->allowedSizes())
                ."`, not `$this->size`");
        }
    }
}