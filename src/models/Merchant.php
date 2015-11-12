<?php

/*
 * Finance Plugin for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-finance
 * @package   hipanel-module-finance
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2015, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\finance\models;

class Merchant extends \hipanel\base\Model
{
    use \hipanel\base\ModelTrait;
    use \hiqdev\yii2\merchant\models\MerchantTrait;
}