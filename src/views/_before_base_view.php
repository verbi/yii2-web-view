<?php
use verbi\yii2Helpers\Html;

echo Html::pageHeading($this->title);
echo Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]);
echo Html::beginPageWrapperDivision();
echo Html::showFlash();