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
     * @return \XF\Mvc\Entity\AbstractCollection|\XF\Mvc\Entity\Entity|null
     *
     * we need to override this due to composite key usage
     */
    public function getContent($id)
    {
        $finder = \XF::finder('West\UserApiKey:UserApiKey')
            ->where('api_key_id', $id)
            ->keyedBy('api_key_id');

        return \is_array($id) ? $finder->fetch() : $finder->fetchOne();
    }
}