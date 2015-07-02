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
     * text.
     *
     * @var array
     */
    public $multiplicateButtonOptions = array();

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
        $cs->registerScriptFile($this->_core->assetUrl.'/js/imagetransfer.js');
        $cs->registerScript('init-uploader', 'var uploader = new ImageUploader({'
            .'maxWidth:'.(isset($this->imageOptions['width']) ? $this->imageOptions['width'] : 'null').','
            .'maxHeight:'.(isset($this->imageOptions['height']) ? $this->imageOptions['height'] : 'null')
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
            'tag' => 'li',
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

            CHtml::button($label, $mBtnOptions);

            $imageHtmlOptions = isset($this->htmlOptions) ? $this->htmlOptions : array();
            $imageHtmlOptions['class'] = isset($imageHtmlOptions['class']) ?
                $imageHtmlOptions['class'] .= ' imgtrgt-wrapper'
                : $imageHtmlOptions['class'] = 'imgtrgt-wrapper';

            Yii::app()->getClientScript()
                ->registerScript('init-multiplicator', 'uploader.setFieldData('
                    .json_encode(array(
                        'wrapper' => $imageHtmlOptions,
                        'img' => $this->imageOptions,
                        'input' => array('name' => $inputData['name']),
                    ))
                .')');
        }

        echo CHtml::openTag('ul');

        if (empty($this->codes)) {
            $this->widget('imgtr.widgets.ImageGetter', array_merge(array(
                'code' => null,
            ), $widgetData));

            echo CHtml::tag('input', $inputData);
        } else {
            foreach ($this->codes as $code) {
                $this->widget('imgtr.widgets.ImageGetter', array_merge(array(
                    'code' => $code,
                ), $widgetData));
                echo CHtml::tag('input', $inputData);
            }
        }
        echo CHtml::closeTag('ul');
        echo CHtml::closeTag($this->tag);
    }

    protected function checkData()
    {
        if (!in_array($this->size, $this->_core->allowedSizes())) {
            throw new YiiITException('Size can be only `'
                .implode('`, `', $this->_core->allowedSizes())
                ."`, not `$this->size`");
        }
    }
}
