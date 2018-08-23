<?php

namespace UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class UserBundle extends Bundle
{
    /**
     * Inherit from SabrusBaseUserBundle which inherits from FOSUserBundle.
     *
     * @return string
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
