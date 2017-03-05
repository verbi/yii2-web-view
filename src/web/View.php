<?php

namespace verbi\yii2WebView\web;
use \Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\base\Widget;
use verbi\yii2Helpers\widgets\Pjax;
use verbi\yii2Helpers\Html;
class View extends \yii\web\View {

    protected $beforeRenderPhpFileBaseView;
    protected $afterRenderPhpFileBaseView;
    protected $registerWidgetStack;
    public $returnLinkUrl;
    public $returnLinkText;

    public function init() {
        parent::init();
        if (!$this->getBeforeRenderPhpFileBaseView()) {
            $this->setBeforeRenderPhpFileBaseView('@vendor/verbi/yii2-web-view/_before_base_view');
        }
        if (!$this->getAfterRenderPhpFileBaseView()) {
            $this->setAfterRenderPhpFileBaseView('@vendor/verbi/yii2-web-view/_after_base_view');
        }
        $this->initTitle();
        $this->initBreadcrumbs();
    }

    public function initTitle() {
        if (!$this->title && Yii::$app->controller) {
            $this->title = Yii::t('verbi', Inflector::camel2words(StringHelper::basename(Yii::$app->controller->action->id)) . ' ' . Inflector::camel2words(StringHelper::basename(Yii::$app->controller->id)));
        }
    }

    public function initBreadcrumbs() {
        if(Yii::$app->controller) {
            if(Yii::$app->controller->module) {
                $this->params['breadcrumbs'][] = [
                    'label' => Yii::t('verbi', Inflector::camel2words(StringHelper::basename(Yii::$app->controller->module->id))),
                    'url' => ['/'.Yii::$app->controller->module->id.'/'.Yii::$app->controller->module->defaultRoute],
                ];
            }
            $this->params['breadcrumbs'][] = [
                'label' => Yii::t('verbi', Inflector::pluralize(Inflector::camel2words(StringHelper::basename(Yii::$app->controller->id)))),
                'url' => ['/' . Yii::$app->controller->getUniqueId() . '/index'],
            ];
            $this->params['breadcrumbs'][] = Yii::t( 'verbi', Yii::$app->controller->action->id );
        }
    }

    public function getBeforeRenderPhpFileBaseView() {
        return $this->beforeRenderPhpFileBaseView;
    }

    public function setBeforeRenderPhpFileBaseView($value) {
        $this->beforeRenderPhpFileBaseView = $value;
    }

    public function getAfterRenderPhpFileBaseView() {
        return $this->afterRenderPhpFileBaseView;
    }

    public function setAfterRenderPhpFileBaseView($value) {
        $this->afterRenderPhpFileBaseView = $value;
    }

    public function renderPhpFile($_file_, $_params_ = []) {
        $output = '';
        $beforeViewFile = $this->findViewFile($this->getBeforeRenderPhpFileBaseView());
        if ($this->getBeforeRenderPhpFileBaseView() && is_file($beforeViewFile)) {
            $output .= parent::renderPhpFile($beforeViewFile, $_params_);
        }
        $output .= parent::renderPhpFile($_file_, $_params_);
        $afterViewFile = $this->findViewFile($this->getAfterRenderPhpFileBaseView());
        if ($this->getAfterRenderPhpFileBaseView() && is_file($afterViewFile)) {
            $output .= parent::renderPhpFile($afterViewFile, $_params_);
        }
        return $output;
    }
    
    protected function &getRegisterWidgetStack() {
            $this->registerWidgetStack = array_filter(Widget::$stack,
            function(&$item) {
                if($item instanceof Pjax) {
                    return true;
                }
                return false;
            });
        return $this->registerWidgetStack;
    }

    public function registerAssetBundle($name, $position = null)
    {
        array_walk(
            $this->getRegisterWidgetStack(),
            function(&$item) use (&$name, &$position) {
                $item->registerAssetBundle($name, $position);
            }
        );
        $bundle = parent::registerAssetBundle($name, $position);
        return $bundle;
    }
    
    public function registerLinkTag($options, $key = null)
    {
        array_walk(
            $this->getRegisterWidgetStack(),
            function(&$item) use (&$options, &$key) {
                $item->registerLinkTag($options, $key);
            }
        );
        parent::registerLinkTag($options, $key);
    }
    
    public function registerCss($css, $options = [], $key = null)
    {
        array_walk(
            $this->getRegisterWidgetStack(),
            function(&$item) use (&$css, &$options, &$key) {
                $item->registerCss($css, $options, $key);
            }
        );
        parent::registerCss($css, $options, $key);
    }
    
    public function registerCssFile($url, $options = [], $key = null)
    {
        array_walk(
            $this->getRegisterWidgetStack(),
            function(&$item) use (&$url, &$options, &$key) {
                $item->registerCssFile($url, $options, $key);
            }
        );
        parent::registerCssFile($url, $options, $key);
    }
    
    public function registerJs($js, $position = self::POS_READY, $key = null)
    {
        array_walk(
            $this->getRegisterWidgetStack(),
            function(&$item) use (&$js, &$position, &$key) {
                $item->registerJs($js, $position, $key);
            }
        );
        parent::registerJs($js, $position, $key);
    }
    
    public function registerJsFile($url, $options = [], $key = null)
    {
        array_walk(
            $this->getRegisterWidgetStack(),
            function(&$item) use (&$url, &$options, &$key) {
                $item->registerJsFile($url, $options, $key);
            }
        );
        $jsOptions = [
            'async' => 'async',
        ];
        parent::registerJsFile($url, array_merge($jsOptions, $options), $key);
    }
    
        protected function renderHeadHtml()
    {
        $lines = [];
        if (!empty($this->metaTags)) {
            $lines[] = implode('', $this->metaTags);
        }
        if (!empty($this->linkTags)) {
            $lines[] = implode('', $this->linkTags);
        }
        if (!empty($this->cssFiles)) {
            $lines[] = implode('', $this->cssFiles);
        }
        if (!empty($this->css)) {
            $lines[] = implode('', $this->css);
        }
        if (!empty($this->jsFiles[self::POS_HEAD])) {
            $lines[] = implode('', $this->jsFiles[self::POS_HEAD]);
        }
        if (!empty($this->js[self::POS_HEAD])) {
            $lines[] = Html::script(implode('', $this->js[self::POS_HEAD]), ['type' => 'text/javascript']);
        }
        return empty($lines) ? '' : implode('', $lines);
    }
    
    protected function renderBodyBeginHtml()
    {
        $lines = [];
        if (!empty($this->jsFiles[self::POS_BEGIN])) {
            $lines[] = implode('', $this->jsFiles[self::POS_BEGIN]);
        }
        if (!empty($this->js[self::POS_BEGIN])) {
            $lines[] = Html::script(implode('', $this->js[self::POS_BEGIN]), ['type' => 'text/javascript']);
        }
        return empty($lines) ? '' : implode('', $lines);
    }
    
    protected function renderBodyEndHtml($ajaxMode)
    {
        $lines = [];
        if (!empty($this->jsFiles[self::POS_END])) {
            $lines[] = implode('', $this->jsFiles[self::POS_END]);
        }
        if ($ajaxMode) {
            $scripts = [];
            if (!empty($this->js[self::POS_END])) {
                $scripts[] = implode('', $this->js[self::POS_END]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $scripts[] = implode('', $this->js[self::POS_READY]);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $scripts[] = implode('', $this->js[self::POS_LOAD]);
            }
            if (!empty($scripts)) {
                $lines[] = Html::script(implode('', $scripts), ['type' => 'text/javascript']);
            }
        } else {
            if (!empty($this->js[self::POS_END])) {
                $lines[] = Html::script(implode('', $this->js[self::POS_END]), ['type' => 'text/javascript']);
            }
            if (!empty($this->js[self::POS_READY])) {
                $js = "jQuery(document).ready(function () {" . implode('', $this->js[self::POS_READY]) . "});";
                $lines[] = Html::script($js, ['type' => 'text/javascript']);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $js = "jQuery(window).on('load', function () {" . implode('', $this->js[self::POS_LOAD]) . "});";
                $lines[] = Html::script($js, ['type' => 'text/javascript']);
            }
        }
        return empty($lines) ? '' : implode('', $lines);
    }
}
