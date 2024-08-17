<?php

namespace West\UserApiKey\Job;

use West\UserApiKey\Entity\UserStore;
use XF\Job\AbstractJob;

class StoreCheckSingle extends AbstractJob
{
    use StoreCheckTrait;

    protected $defaultData = [
        'userId' => null
    ];

    public function run($maxRunTime)
    {
        $userId = $this->data['userId'];
        if (!$userId)
        {
            return $this->complete();
        }

        /** @var UserStore $store */
        $store = $this->app->find('West\UserApiKey:UserStore', $userId);
        if ($store)
        {
            $this->checkStore($store);
        }

        return $this->complete();
    }

    public function getStatusMessage()
    {
        return sprintf('%s... (%d)', \XF::phrase('wuak_checking_stores'), $this->data['userId']);
    }

    public function canCancel()
    {
        return false;
    }

    public function canTriggerByChoice()
    {
        return false;
    }
}