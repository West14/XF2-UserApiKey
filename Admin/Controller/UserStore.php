<?php

namespace West\UserApiKey\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class UserStore extends AbstractController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('wuakManage');
    }

    public function actionIndex()
    {
        $page = $this->filterPage();
        $perPage = 50;

        $storeFinder = $this->getUserStoreRepo()->findUserStoresForList();

        $total = $storeFinder->total();
        $this->assertValidPage($page, $perPage, $total, 'wuak-stores');

        $storeFinder->limitByPage($page, $perPage);

        return $this->view('West\UserApiKey:UserStore\Index', 'wuak_user_store_index', [
            'storeList' => $storeFinder->fetch(),

            'page' => $page,
            'perPage' => $perPage,
            'total' => $total
        ]);
    }

    public function actionEdit(ParameterBag $params)
    {
        $store = $this->assertUserStoreExists($params->user_id, 'User');

        return $this->view('West\UserApiKey:UserStore\Edit', 'wuak_user_store_edit', [
            'store' => $store
        ]);
    }

    public function actionToggle()
    {
        /** @var \XF\ControllerPlugin\Toggle $plugin */
        $plugin = $this->plugin('XF:Toggle');
        return $plugin->actionToggle('West\UserApiKey:UserStore');
    }

    public function actionSave(ParameterBag $params)
    {
        /** @var \West\UserApiKey\Entity\UserStore $store */
        $store = $this->assertUserStoreExists($params->user_id);

        $this->storeSaveProcess($store)->run();

        return $this->redirect($this->buildLink('wuak-stores') . $this->buildLinkHash($store->user_id));
    }

    public function actionSellerList()
    {
        $sellerUserGroups = \XF::options()->wuakSellerUserGroups;
        $sellerFinder = \XF::finder('XF:User')
            ->with('UserStore');

        $expressions = array_map(function (int $userGroupId) use ($sellerFinder)
        {
            return $sellerFinder->expression(
                sprintf('FIND_IN_SET(%s, %%s)', $sellerFinder->quote($userGroupId)),
                ['secondary_group_ids']
            );
        }, $sellerUserGroups);

        $sellerList = $sellerFinder->whereOr($expressions)
            ->fetch();

        $mainSellerList = [];
        $otherSellerList = [];
        $manuallyVerifiedList = [];

        $otherSellerIds = $this->getOtherSellerIds();

        /** @var \West\UserApiKey\XF\Entity\User $seller */
        foreach ($sellerList as $seller)
        {
            if ($seller->UserStore && $seller->UserStore->disable_auto_check)
            {
                $manuallyVerifiedList[] = $seller;
            }

            if (in_array($seller->user_id, $otherSellerIds))
            {
                $otherSellerList[] = $seller;
            }
            else
            {
                $mainSellerList[] = $seller;
            }
        }

        return $this->view('West\UserApiKey:UserStore\SellerList', 'wuak_user_store_seller_list', [
            'mainSellerList' => $mainSellerList,
            'otherSellerList' => $otherSellerList,
            'manuallyVerifiedList' => $manuallyVerifiedList
        ]);
    }

    public function actionSellerListToggle()
    {
        $this->assertPostOnly();

        $sellerId = $this->filter('user_id', 'uint');
        if (!$sellerId)
        {
            return $this->notFound();
        }

        $otherSellerIds = $this->getOtherSellerIds();
        if (in_array($sellerId, $otherSellerIds))
        {
            $otherSellerIds = array_filter($otherSellerIds, function ($userId) use ($sellerId)
            {
                return $userId != $sellerId;
            });
        }
        else
        {
            $otherSellerIds[] = $sellerId;
        }

        $this->updateOtherSellerIds($otherSellerIds);

        return $this->redirect($this->getDynamicRedirect());
    }

    protected function storeSaveProcess(\West\UserApiKey\Entity\UserStore $store)
    {
        $input = $this->filter([
            'store_url' => 'str',
            'webhook_url' => 'str',
            'active' => 'bool',
            'disable_auto_check' => 'bool',
            'status' => 'str'
        ]);

        $store->setOption('user_edit', false);

        return $this->formAction()->basicEntitySave($store, $input);
    }

    protected function assertUserStoreExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists('West\UserApiKey:UserStore', $id, $with, $phraseKey);
    }

    /**
     * @return \XF\Mvc\Entity\Repository|\West\UserApiKey\Repository\UserStore
     */
    protected function getUserStoreRepo()
    {
        return $this->repository('West\UserApiKey:UserStore');
    }

    protected function getOtherSellerIds(): array
    {
        return \XF::app()
            ->simpleCache()
            ->getValue('West/UserApiKey', 'otherSellerIds') ?? [];
    }

    protected function updateOtherSellerIds(array $otherSellerIds)
    {
        \XF::app()
            ->simpleCache()
            ->setValue('West/UserApiKey', 'otherSellerIds', $otherSellerIds);
    }
}