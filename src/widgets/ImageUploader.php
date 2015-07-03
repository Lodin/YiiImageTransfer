<?php

class ImageUploader extends CWidget
{
    /**
     * List of already uploaded images codes to display it for reloading. Codes
     * are created at uploading operation.
     *
     * @var array
     */
    public $codes = array();

    /**
     * Allows image uploading fields multiplication by adding special button.
     *
     * @var bool
     */
    public $multiplicate = false;

    /**
     * Image size to display images defined in `$codes` property.
     *
     * @var string
     */
    public $size = '';

    /**
     * Directory under the main image directory separating image by different
     * types.
     *
     * @var string
     */
    public $subdir = '';

    /**
     * Image wrapper tag html options.
     *
     * @var array
     */
    public $htmlOptions = array();

    /**
     * Customizes image wrapper tag.
     *
     * @var array
     */
    public $imageWrapperOptions = array();

    /**
     * Image or it's placeholder options.
     *
     * @var array
     */
    public $imageOptions = array();

    /**
     * Uploading model.
     *
     * @var CActiveRecord
     */
    public $model = null;

    /**
     * Defines tag which can be set to wrapper. By default it is `div`, but
     * can be set e.g. to `li`, if the list is need.
     *
     * @var string
     */
    public $tag = 'div';

    /**
     * Model attribute name.
     *
     * @var string
     */
    public $attribute = '';

    /**
     * Multiplicate button html options. Key `label` can contain button label
     * text, and key `position` can define, where button will be appear: before
     * or after main code. 
     *
     * @var array
     */
    public $multiplicateButtonOptions = array();
    
    /**
     * Defines maximal count of images user can upload at one operation
     * 
     * @var integer
     */
    public $max = 1;
    
    /**
     * Defines how many places for image uploading should be created from start
     * 
     * @var integer
     */
    public $defaultCount = 1;
    
    /**
     * Tip for uploading field
     * 
     * @var string
     */
    public $tip = 'Click to upload image';

    /**
     * User defined alias for YiiImageTransfer plugin. Default is
     * `imageTransfer`.
     *
     * @var string
     */
    public $alias = 'imageTransfer';

    protected $_core;

    public function init()
    {
        $this->_core = Yii::app()->{$this->alias};
        $this->checkData();

        $cs = Yii::app()->getClientScript();
        $cs->registerScriptFile("/{$this->_core->assetUrl}/js/imagetransfer.js");
        $cs->registerScript('init-uploader', 'var uploader = new ImageUploader({'
            .'maxWidth:'.(isset($this->imageOptions['width']) ?
                $this->imageOptions['width']
                : $this->_core->sizes[$this->size]['width']).','
            .'maxHeight:'.(isset($this->imageOptions['height']) ?
                $this->imageOptions['height']
                : $this->_core->sizes[$this->size]['height'])
            .'});'
            .'uploader.handleUploading();');
    }

    public function run()
    {
        $htmlOptions = isset($this->htmlOptions) ? $this->htmlOptions : array();
        $htmlOptions['class'] = isset($htmlOptions['class']) ?
            $htmlOptions['class'] .= ' imgtrup-wrapper'
            : $htmlOptions['class'] = 'imgtrup-wrapper';

        $widgetData = array(
            'subdir' => $this->subdir,
            'size' => $this->size,
            'htmlOptions' => $this->imageWrapperOptions,
            'imageOptions' => $this->imageOptions,
            'alias' => $this->alias,
        );

        $inputData = array(
            'type' => 'file',
            'class' => 'imgtrup-input',
            'name' => get_class($this->model)."[{$this->attribute}]",
        );

        if ($this->multiplicate || count($this->codes) > 1) {
            $inputData['name'] .= '[]';
        }

        echo CHtml::openTag($this->tag, $htmlOptions);

        if ($this->multiplicate) {
            $mBtnOptions = isset($this->multiplicateButtonOptions) ?
                $this->multiplicateButtonOptions
                : array();

            if (isset($mBtnOptions['label'])) {
                $label = $mBtnOptions['label'];
                unset($mBtnOptions['label']);
            } else {
                $label = 'Add';
            }

            $mBtnOptions['class'] = isset($mBtnOptions['class']) ?
                $mBtnOptions['class'] .= ' imgtrup-btn-multiplicate'
                : $mBtnOptions['class'] = 'imgtrup-btn-multiplicate';

            $mbtn = CHtml::button($label, $mBtnOptions);

            $imageHtmlOptions = isset($this->htmlOptions) ? $this->htmlOptions : array();
            $imageHtmlOptions['class'] = isset($imageHtmlOptions['class']) ?
                $imageHtmlOptions['class'] .= ' imgtrgt-wrapper'
                : $imageHtmlOptions['class'] = 'imgtrgt-wrapper';

            $placeholder = $this->_core->getPlaceholder();
            $placeholder->bind($this->_core);
            $placeholder->setSize($this->size);
            
            Yii::app()->getClientScript()
                ->registerScript('init-multiplicator', 'uploader.setFieldData('
                    .json_encode(array(
                        'wrapper' => $imageHtmlOptions,
                        'img' => array_merge(
                            array(
                                'src' => $placeholder->url,
                                'width' => $placeholder->width,
                                'height' => $placeholder->height
                            ),
                            $this->imageOptions
                        ),
                        'input' => array('name' => $inputData['name']),
                        'max' => $this->max,
                        'tip' => $this->tip
                    ))
                .');'
                . 'uploader.handleMultiplication();');
        }
        
        if($this->multiplicate
            && isset($this->multiplicateButtonOptions)
            && $this->multiplicateButtonOptions['position'] === 'before') {
            echo $mbtn;
        }
        
        echo CHtml::openTag('ul');

        if (empty($this->codes)) {
            for ($i = 0; $i < $this->defaultCount; $i++) {
                echo CHtml::openTag('li');
                $this->widget('imgtr.widgets.ImageGetter', array_merge(array(
                    'code' => null,
                ), $widgetData));

                echo CHtml::tag('input', $inputData);
                echo CHtml::tag('div', ['class' => 'imgtrup-tip'], $this->tip);
                echo CHtml::closeTag('li');
            }
        } else {
            foreach ($this->codes as $code) {
                echo CHtml::openTag('li');
                $this->widget('imgtr.widgets.ImageGetter', array_merge(array(
                    'code' => $code,
                ), $widgetData));
                echo CHtml::tag('input', $inputData);
                echo CHtml::tag('div', ['class' => 'imgtrup-tip'], $this->tip);
                echo CHtml::closeTag('li');
            }
        }
        echo CHtml::closeTag('ul');
        
        if($this->multiplicate
            && isset($this->multiplicateButtonOptions)
            && $this->multiplicateButtonOptions['position'] === 'after') {
            echo $mbtn;
        }
        
        echo CHtml::closeTag($this->tag);
    }

    protected function checkData()
    {
        if (!in_array($this->size, $this->_core->allowedSizes())) {
            throw new YiiITException('Size can be only `'
                .implode('`, `', $this->_core->allowedSizes())
                ."`, not `$this->size`");
        }
        
        if($this->model === null || empty($this->attribute)) {
            throw new YiiITException('Model and it\'s attribute should be defined');
        }
        
        if(!property_exists(get_class($this->model), $this->attribute)) {
            throw new YiiITException('Model `'.get_class($this->model)
                ."` does not have attribute `{$this->attribute}`");
        }
    }
}