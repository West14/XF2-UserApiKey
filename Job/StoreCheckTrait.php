<?php

namespace West\UserApiKey\Job;

use West\UserApiKey\Entity\UserStore;
use West\UserApiKey\Service\Store\Checker;

trait StoreCheckTrait
{
    protected function checkStore(UserStore $store)
    {
        $store->setOption('user_edit', false);

        /** @var Checker $checker */
        $checker = $this->app->service('West\UserApiKey:Store\Checker', $store);
        $checkResult = $checker->check($errorCode);

        if ($checkResult == 'error' && $store->status == 'valid' && $store->error_retry_count)
        {
            $store->error_retry_count -= 1;
        }
        else
        {
            $store->status = $checkResult;
            if ($store->error_retry_count != 3)
            {
                $store->error_retry_count = 3; // TODO: option?
            }
        }
        $statusChanged = $store->isChanged('status');

        $store->error_code = $errorCode;
        $store->checked_at = \XF::$time;
        $saved = $store->save();

        if ($statusChanged && $saved)
        {
            $this->getStoreRepo()->logStatusChange($store, $store->status, $errorCode, false);
        }
    }

    protected function getStoreRepo(): \West\UserApiKey\Repository\UserStore
    {
        return \XF::repository('West\UserApiKey:UserStore');
    }
}