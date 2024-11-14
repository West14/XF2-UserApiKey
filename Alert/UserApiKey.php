<?php

namespace West\UserApiKey\Alert;

use XF\Alert\AbstractHandler;

class UserApiKey extends AbstractHandler
{
    public function getOptOutActions()
    {
        return ['expiration'];
    }

    /**
     * @param $id
     * @return \XF\Mvc\Entity\ArrayCollection|\XF\Mvc\Entity\Entity|null
     *
     * we need to override this due to composite key usage
     */
    public function getContent($id)
    {
        return \XF::finder('West\UserApiKey:UserApiKey')
            ->where('api_key_id', $id)
            ->keyedBy('api_key_id')
            ->fetchOne();
    }
}