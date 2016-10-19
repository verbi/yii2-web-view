<?php

namespace verbi\yii2WebView\web;
use \Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
class View extends \yii\web\View {

    protected $beforeRenderPhpFileBaseView;
    protected $afterRenderPhpFileBaseView;
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
        if (!$this->title) {
            $this->title = Yii::t('verbi', Inflector::camel2words(StringHelper::basename(Yii::$app->controller->action->id)) . ' ' . Inflector::camel2words(StringHelper::basename(Yii::$app->controller->id)));
        }
    }

    public function initBreadcrumbs() {
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

}
