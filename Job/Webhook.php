<?php

namespace West\UserApiKey\Job;

use West\UserApiKey\Entity\UserStore;
use XF\Job\AbstractJob;

class Webhook extends AbstractJob
{
    protected $defaultData = [
        'content_type' => null,
        'content_id' => null,
        'owner_user_id' => null,
        'retry_count' => 4,
    ];

    public function run($maxRunTime)
    {
        $contentType = $this->data['content_type'];;
        $contentId = $this->data['content_id'];

        if (!$contentType || !$contentId)
        {
            return $this->complete();
        }

        $ownerUserId = $this->data['owner_user_id'];
        if ($ownerUserId)
        {
            /** @var \West\UserApiKey\XF\Entity\User $user */
            $user = $this->app->find('XF:User', $ownerUserId, 'UserStore');
            $store = $user->UserStore;
        }
        else
        {
            /** @var \West\UserApiKey\Repository\UserStore $userStoreRepo */
            $userStoreRepo = $this->app->repository('West\UserApiKey:UserStore');
            /** @var UserStore $store */
            $store = $userStoreRepo->getStoreByContent($contentType, $contentId);
            $user = $store->User;
        }

        if (!$store || !$store->webhook_url)
        {
            return $this->complete();
        }

        $content = $this->app->findByContentType($contentType, $contentId);
        $json = \XF::asVisitor($user, function () use ($content)
        {
             return $content->toApiResult()->render();
        });

        $reader = \XF::app()->http()->reader();
        $options = ['json' => [$contentType => $json]];
        if ($store->webhook_secret)
        {
            $options['headers']['X-Webhook-Secret'] = $store->webhook_secret;
        }

        $response = $reader->requestUntrusted('POST', $store->webhook_url, [], null, $options, $error);
        if ($error || $response->getStatusCode() != 200)
        {
            $this->data['retry_count']--;
            if (!$this->data['retry_count'])
            {
                return $this->complete(); // give up
            }

            $result = $this->resume();
            $result->continueDate = $this->getContinueDate();

            return $result;
        }

        return $this->complete();
    }

    protected function getContinueDate()
    {
        $delayMap = [
            1 => 60,
            2 => 30,
            3 => 10
        ];

        return \XF::$time + $delayMap[$this->data['retry_count']] * 60;
    }

    public function getStatusMessage()
    {
        return sprintf(
            '%s (%s, %s)',
            \XF::phrase('wuak_sending_webhooks...'),
            $this->data['content_type'],
            $this->data['content_id']
        );
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