<?php

    class PluginReferals_ActionReferals extends ActionPlugin
    {
        protected $user = null;

        public function Init()
        {
            $this->user = $this->User_GetUserCurrent();

            if ($this->user) {
                $user['id']      = $this->user->getId();
                $user['login']   = $this->user->getLogin();
                $user['isAdmin'] = $this->user->isAdministrator();
                $this->user      = $user;
            }
        }

        public function EventShutdown()
        {
            $this->Viewer_Assign('user', $this->user);
        }

        protected function RegisterEvent()
        {
            $this->AddEvent('error', 'EventError');
            $this->AddEventPreg('/^([0-9a-z]+)$/i', 'EventSetCookies');
        }

        protected function EventError()
        {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('plugin.referals.error_registered_yet'), E::ModuleLang()->Get('error'));
            return Router::Action('error');
        }

        protected function EventSetCookies()
        {
            if ($this->user) {

                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('plugin.referals.error_registered_and_logined'), E::ModuleLang()->Get('error'));

            } elseif (!func_check($this->GetParamEventMatch(0), 'login', 3, 30) || !$this->User_GetUserByLogin($this->GetParamEventMatch(0))) {

                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('plugin.referals.broken_link'), E::ModuleLang()->Get('error'));
            } else {

                if (!isset($_COOKIE['ref']) || !isset($_COOKIE['date'])) {
                    setcookie('ref', $this->GetParamEventMatch(0), time() + 60 * 60 * 24, Config::Get('sys.cookie.path'), Config::Get('sys.cookie.host'));
                    setcookie('date', date('Y-m-d', time()), time() + 60 * 60 * 24, Config::Get('sys.cookie.path'), Config::Get('sys.cookie.host'));
                }

                func_header_location(Router::GetPath('index'));
            }

            return Router::Action('error');
        }
    }
