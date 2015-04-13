<?php

namespace YiiImageTransfer;

class TImageGetter extends CWidget
{
    public $id = '';
    public $size = '';
    public $subfolder = '';
    public $htmlOptions = array();

    protected $_core;

    public function init()
    {
        $this->_core = Yii::app()->imageTransfer;

        if (!in_array($this->size, $this->_core->allowedSizes())) {
            throw new YiiITException('Size can be only `'
                .implode('`, `', $this->_core->allowedSizes())
                ."`, not `$this->size`");
        }

        Yii::app()->getClientScript()
            ->registerCssFile($this->_core->assetUrl.'/css/imagetransfer.css');
    }

    public function run()
    {
        $sizes = array(
            'width' => $this->_core->sizes[$this->size]['width'],
            'height' => $this->_core->sizes[$this->size]['height'],
        );

        $image = $this->_core->get($this->id, $this->subfolder, $this->size);

        $optionList = '';

        if ($image->isPlaceholder && $this->size != '') {
            $image->setSize($this->size);
        } else {
            $image->setSize(array(
                'width' => !empty($this->htmlOptions['width']) ?
                    $this->htmlOptions['width']
                    : 10000,
                'height' => !empty($this->htmlOptions['height']) ?
                    $this->htmlOptions['height']
                    : 10000,
            ));
        }
        
        $htmlOptions = isset($this->htmlOptions)? $this->htmlOptions : array();
        $htmlOptions['class'] = isset($htmlOptions['class']) ?
            $htmlOptions['class'] .= ' imgr-wrapper'
            : $htmlOptions['class'] = 'imgr-wrapper';
        
        echo CHtml::openTag('div', $htmlOptions);
        echo CHtml::tag('img', array(
            'src' => $image->relativeUrl,
            'width' => $image->width . 'px',
            'height' => $image->heigh . 'px'
        ));
        
        $this->render('imageloader.view.getter', array(
            'sizes' => $sizes,
            'image' => $image,
            'htmlOptions' => $optionList,
        ));
    }
}
