<?php
/**
 * Created by PhpStorm.
 * User: eugencherniy
 * Date: 2/5/15
 * Time: 5:30 PM
 */

namespace frontend\models\validators;


use common\models\User;
use yii\validators\ExistValidator;

class existFrontEndValidator extends ExistValidator
{
    public function init()
    {
        parent::init();
        $this->filter = ['status' => User::STATUS_ACTIVE];
    }
}
