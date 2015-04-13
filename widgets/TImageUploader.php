<?php

namespace YiiImageTransfer;

class TImageUploader extends CWidget
{
    public $names = null;
    public $multipleMode = false;
    
    public $type = '';
    public $subfolder = '';
    
    public $options;
    
    public $max = null;
    
    public $addingBtnLabel = 'Add';
    
    protected $_core;
    
    public function init()
    {
        $this->_core = Yii::app()->imageTransfer;
        
        $this->checkData();
    }
    
    public function run()
    {
        echo CHtml::openTag('div', array('class' => 'imgtr-uploader'));
        
        if($this->multipleMode) {
            if(isset($this->buttonHtmlOptions['class']))
                $this->buttonHtmlOptions['class'] .= ' ';
            
            echo CHtml::openTag('div', array('class' => 'imgtr-btn-wrapper'));
            echo CHtml::tag('button', $this->htmlOptions['button'], $this->addingBtnLabel);
            echo CHtml::closeTag('div'); // imgtr-btn-wrapper
        }
        
        echo CHtml::openTag('div', array('class' => 'imgtr-list'));
        
        if($this->multipleMode) {
            echo CHtml::tag('input', array(
                'type' => 'hidden',
                'class' => 'imgtr-el-data',
                'value' => json_encode(array(
                    'placeholder' => $this->_core->placeholder,
                    'wrapperOptions' => isset($this->htmlOptions['wrapper']) ?
                        $this->htmlOptions['wrapper']
                        : '',
                    'name' => isset($this->htmlOptions['image']['name']) ?
                        $this->htmlOptions['image']['name']
                        : ''
                ))
            ));
        }
        
        foreach($this->getImages() as $image) {
            $wrapperHtmlOptions = isset($this->htmlOptions['wrapper']) ?
                $this->htmlOptions['wrapper']
                : array();
            
            $imageHtmlOptions = isset($this->htmlOptions['image']) ?
                $this->htmlOptions['image']
                : array();
            
            $wrapperHtmlOptions['class'] = isset($wrapperHtmlOptions['class'])?
                $wrapperHtmlOptions['class'] .= ' imgtr-image'
                : $wrapperHtmlOptions['class'] = 'imgtr-image';

            $imageHtmlOptions['src'] = $image->relativeUrl;
            $imageHtmlOptions['width'] = $image->width;
            $imageHtmlOptions['height'] = $image->height;
            
            CHtml::openTag('div', $wrapperHtmlOptions);
            CHtml::tag('img', $imageHtmlOptions);
            CHtml::closeTag('div');
        }
        
        echo CHtml::closeTag('div'); // imgtr-list
        
        echo CHtml::closeTag('div'); // imgtr-uploader
    }
    
    protected function checkData()
    {
        if(!in_array($this->type, $this->_core->allowedTypes())) {
            throw new YiiITException("Type `{$this->type}` is not allowed");
        }
        
        $allowedOptions = array('button', 'wrapper', 'image');
        $allowedHtmlOptionKey = array('id', 'class', 'name');
        foreach($this->htmlOptions as $optionName => $option) {
            if(!in_array($optionName, $allowedOptions)) {
                throw new YiiITException("Option `$optionName` is not allowed. You"
                    . ' can use: ' . implode(', ', $allowedOptions));
            }
            
            foreach($option as $key => $val) {
                if(!in_array($key, $allowedHtmlOptionKey)) {
                    throw new YiiITException("`$optionName` can"
                        . ' contain only ' . implode(', ', $allowedHtmlOptionKey));
                }
            }
        }
        
        if(empty($this->addingBtnLabel))
            throw new YiiITException('Adding button label should be defined');
    }
    
    protected function getImages()
    {
        if($this->multipleMode) {
            $i = 0;
            $images = array();
            
            if (is_array($this->names)) {
                foreach ($this->names as $imgID) {
                    if ($this->max !== null && $i > $this->max)
                        break;

                    $images[] = $this->_core->get($imgID, $this->subfolder, $this->size);            
                    if ($imgID === null)
                        $images[count($images) - 1]->setSize($this->size);

                    $i++;
                }
            } else {
                $images[] = $this->_core->get($this->names, $this->subfolder, $this->size);          
                if ($this->names === null)
                    $images[count($images) - 1]->setSize($this->size);
            }

            return $images;
        } else {
            return array($this->_core->get($this->names, $this->subfolder, $this->type));
        }
    }
}
