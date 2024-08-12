<?php

namespace West\UserApiKey\Alert;

use XF\Alert\AbstractHandler;

class UserApiKey extends AbstractHandler
{
    public function getOptOutActions()
    {
        return ['expiration'];
    }

    public function getContent($id)
    {
        return \XF::finder('West\UserApiKey:UserApiKey')
            ->where('api_key_id', $id)
            ->keyedBy('api_key_id')
            ->fetch();
    }
}