<?php

    class PluginReferals_WidgetReferals extends Widget
    {
        public function Exec()
        {
            $sUserProfileLogin = Router::getActionEvent();
            $oUserProfile      = $this->User_GetUserByLogin($sUserProfileLogin);

            $referals = $this->PluginReferals_Referals_getReferals($oUserProfile->getId());

            $this->Viewer_Assign('referals', $referals);
            $this->Viewer_Assign('oUserProfile', $oUserProfile);
            $this->Viewer_Assign('user', $this->User_GetUserCurrent());
            return $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__) . 'widgets/referals.tpl');
        }
    }
