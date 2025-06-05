<?php

namespace West\UserApiKey\Job;

use West\UserApiKey\Entity\UserStore;
use XF\Job\AbstractJob;

class StoreCheck extends AbstractJob
{
    use StoreCheckTrait;

    protected $defaultData = [
        'count' => 0,
        'position' => 0
    ];

    public function run($maxRunTime)
    {
        $start = microtime(true);

        $db = $this->app->db();

        $userIds = $db->fetchAllColumn('
			SELECT user_id
			FROM xf_wuak_user_store
			WHERE user_id > ? AND disable_auto_check = 0
			ORDER BY user_id
			LIMIT 50
		', [$this->data['position']]);

        if (!$userIds)
        {
            return $this->complete();
        }

        $loopFinished = true;

        foreach ($userIds as $userId)
        {
            $this->data['count']++;
            $this->data['position'] = $userId;

            /** @var UserStore $store */
            $store = $this->app->find('West\UserApiKey:UserStore', $userId);
            if ($store)
            {
                $this->checkStore($store);
            }

            if (microtime(true) - $start >= $maxRunTime)
            {
                $loopFinished = false;
                break;
            }
        }

        if ($loopFinished)
        {
            if (!$db->fetchOne(
                'SELECT 1 FROM xf_wuak_user_store WHERE user_id > ? AND disable_auto_check = 1 LIMIT 1',
                [$this->data['position']]
            ))
            {
                return $this->complete();
            }
        }

        return $this->resume();
    }

    public function getStatusMessage()
    {
        return sprintf('%s... (%d)', \XF::phrase('wuak_checking_stores'), $this->data['position']);
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