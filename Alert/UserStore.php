<?php

namespace West\UserApiKey\Alert;

use XF\Alert\AbstractHandler;

class UserStore extends AbstractHandler
{
    public function getOptOutActions()
    {
        return ['status_change'];
    }
}