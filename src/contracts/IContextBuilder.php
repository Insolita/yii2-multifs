<?php
/**
 * Created by solly [04.06.17 7:03]
 */

namespace insolita\multifs\contracts;

/**
 * Interface IContextBuilder
 */
interface IContextBuilder
{
    /**
     * @return \insolita\multifs\entity\Context
     */
    public function build();
}
