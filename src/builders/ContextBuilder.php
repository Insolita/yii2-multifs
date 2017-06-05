<?php
/**
 * Created by solly [04.06.17 7:05]
 */

namespace insolita\multifs\builders;

use insolita\multifs\contracts\IContextBuilder;
use insolita\multifs\entity\Context;

/**
 * Class ContextBuilder
 */
class ContextBuilder implements IContextBuilder
{
    /**
     * @return \insolita\multifs\entity\Context
     */
    public function build()
    {
        $identity = \Yii::$app->user->getIsGuest() ? null : \Yii::$app->user->getIdentity();
        return new Context(\Yii::$app->homeUrl,
                           \Yii::$app->controller->getRoute(),
                           \Yii::$app->getRequest()->getQueryParams(),
                           $identity);
    }
}
