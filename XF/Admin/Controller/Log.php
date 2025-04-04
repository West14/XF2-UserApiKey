<?php

namespace West\UserApiKey\XF\Admin\Controller;

class Log extends XFCP_Log
{
    public function actionWuakUserStore()
    {
        $this->setSectionContext('wuakStoreCheckLog');
        $logId = $this->filter('log_id', 'int');
        if ($logId)
        {
            $entry = $this->em()->find('West\UserApiKey:StoreCheckLog', $logId);
            if (!$entry)
            {
                return $this->notFound();
            }

            return $this->view('West\UserApiKey:Log\UserStore\Html', 'wuak_log_store_check_html', [
                'log' => $entry
            ]);
        }

        $page = $this->filterPage();
        $perPage = 50;

        $logFinder = $this->finder('West\UserApiKey:StoreCheckLog')
            ->limitByPage($page, $perPage)
            ->order('log_date', 'DESC');


        return $this->view('West\UserApiKey:Log\UserStore\Index', 'wuak_log_store_check', [
            'logs' => $logFinder->fetch(),

            'page' => $page,
            'perPage' => $perPage,
            'total' => $logFinder->total(),
        ]);
    }
}