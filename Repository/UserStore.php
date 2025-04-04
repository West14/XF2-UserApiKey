<?php

namespace West\UserApiKey\Repository;

use West\UserApiKey\Entity\StoreCheckLog;
use West\UserApiKey\Entity\UserStore as UserStoreEntity;
use XF\Mvc\Entity\Repository;

class UserStore extends Repository
{
    public function findUserStoresForList()
    {
        return $this->finder('West\UserApiKey:UserStore')
            ->with('User');
    }

    public function logStatusChange(UserStoreEntity $store, string $newStatus, string $errorCode = null, bool $newTransaction = true, ?string $html = null)
    {
        /** @var StoreCheckLog $logEntry */
        $logEntry = $this->em->create('West\UserApiKey:StoreCheckLog');
        $logEntry->user_id = $store->user_id;
        $logEntry->store_url = $store->store_url;
        $logEntry->status = $newStatus;
        $logEntry->error_code = $errorCode;
        $logEntry->html = $html;
        $logEntry->save(true, $newTransaction);

        return $logEntry;
    }

    public function getStoreByContent(string $contentType, $contentId)
    {
        try
        {
            $content = \XF::app()->findByContentType($contentType, $contentId, 'User');
        }
        catch (\LogicException $e) // in case if the User relation doesn't exist
        {
            \XF::logException($e);
            return null;
        }

        return $content->User ? $content->User->UserStore : null;
    }
}