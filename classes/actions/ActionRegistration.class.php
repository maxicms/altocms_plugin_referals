<?php

    class PluginReferals_ActionRegistration extends PluginReferals_Inherit_ActionRegistration
    {
        protected function EventIndex()
        {
            if (isset($_COOKIE['reg'])) {
                func_header_location(Router::GetPath('ref/error'));
            }
        }

        protected function _addReferal($referal_login)
        {
            if (isset($_COOKIE['ref']) && isset($_COOKIE['date']) && func_check($_COOKIE['ref'], 'login', 3, 30)) {
                $user = $this->User_GetUserByLogin($_COOKIE['ref']);
                if ($user) {
                    $referal = $this->User_GetUserByLogin($referal_login);
                    $this->PluginReferals_Referals_addReferal($user->getId(), $referal->getId(), $_COOKIE['date']);
                    setcookie('reg', 1, time() + 60 * 60 * 24 * 365, Config::Get('sys.cookie.path'), Config::Get('sys.cookie.host'));
                    //$this->PluginReferals_Referals_sendPM($user->getId(), $this->Lang_Get('referals_referal_registered'), $this->Lang_Get('referals_referal_registered_message') . ' ' . $referal_login);
                }
            }
        }

        protected function EventActivate()
        {
            $bError = false;
            /**
             * Проверяет передан ли код активации
             */
            $sActivateKey = $this->GetParam(0);
            if (!F::CheckVal($sActivateKey, 'md5')) {
                $bError = true;
            }
            /**
             * Проверяет верный ли код активации
             */
            if (!($oUser = E::ModuleUser()->GetUserByActivateKey($sActivateKey))) {
                $bError = true;
            }
            /**
             *
             */
            if ($oUser && $oUser->getActivate()) {
                E::ModuleMessage()->AddErrorSingle(
                    E::ModuleLang()->Get('registration_activate_error_reactivate'), E::ModuleLang()->Get('error')
                );
                return R::Action('error');
            }
            /**
             * Если что то не то
             */
            if ($bError) {
                E::ModuleMessage()->AddErrorSingle(
                    E::ModuleLang()->Get('registration_activate_error_code'), E::ModuleLang()->Get('error')
                );
                return R::Action('error');
            }
            /**
             * Активируем
             */
            $oUser->setActivate(1);
            $oUser->setDateActivate(F::Now());
            /**
             * Сохраняем юзера
             */
            if (E::ModuleUser()->Update($oUser)) {
                $this->DropInviteRegister();
                E::ModuleViewer()->Assign('bRefreshToHome', true);
                E::ModuleUser()->Authorization($oUser, false);

                $this->_addReferal($oUser->getLogin());

                return;
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
                return R::Action('error');
            }
        }

        protected function EventAjaxRegistration()
        {

            // * Устанавливаем формат Ajax ответа
            E::ModuleViewer()->SetResponseAjax('json');

            E::ModuleSecurity()->ValidateSendForm();

            // * Создаем объект пользователя и устанавливаем сценарий валидации
            /** @var ModuleUser_EntityUser $oUser */
            $oUser = E::GetEntity('ModuleUser_EntityUser');
            $oUser->_setValidateScenario('registration');

            // * Заполняем поля (данные)
            $oUser->setLogin($this->GetPost('login'));
            $oUser->setMail($this->GetPost('mail'));
            $oUser->setPassword($this->GetPost('password'));
            $oUser->setPasswordConfirm($this->GetPost('password_confirm'));
            $oUser->setCaptcha($this->GetPost('captcha'));
            $oUser->setDateRegister(F::Now());
            $oUser->setIpRegister(F::GetUserIp());

            // * Если используется активация, то генерим код активации
            if (Config::Get('general.reg.activation')) {
                $oUser->setActivate(0);
                $oUser->setActivateKey(F::RandomStr());
            } else {
                $oUser->setActivate(1);
                $oUser->setActivateKey(null);
            }
            E::ModuleHook()->Run('registration_validate_before', array('oUser' => $oUser));

            // * Запускаем валидацию
            if ($oUser->_Validate()) {
                // Сбросим капчу // issue#342.
                E::ModuleSession()->Drop(E::ModuleCaptcha()->GetKeyName());

                E::ModuleHook()->Run('registration_validate_after', array('oUser' => $oUser));
                $oUser->setPassword($oUser->getPassword(), true);
                if (E::ModuleUser()->Add($oUser)) {
                    E::ModuleHook()->Run('registration_after', array('oUser' => $oUser));

                    // * Подписываем пользователя на дефолтные события в ленте активности
                    E::ModuleStream()->SwitchUserEventDefaultTypes($oUser->getId());

                    // * Если юзер зарегистрировался по приглашению то обновляем инвайт
                    if (Config::Get('general.reg.invite') && ($oInvite = E::ModuleUser()->GetInviteByCode($this->GetInviteRegister()))) {
                        $oInvite->setUserToId($oUser->getId());
                        $oInvite->setDateUsed(F::Now());
                        $oInvite->setUsed(1);
                        E::ModuleUser()->UpdateInvite($oInvite);
                    }

                    // * Если стоит регистрация с активацией то проводим её
                    if (Config::Get('general.reg.activation')) {
                        // * Отправляем на мыло письмо о подтверждении регистрации
                        E::ModuleNotify()->SendRegistrationActivate($oUser, F::GetRequestStr('password'));
                        E::ModuleViewer()->AssignAjax('sUrlRedirect', R::GetPath('registration') . 'confirm/');
                    } else {
                        E::ModuleNotify()->SendRegistration($oUser, F::GetRequestStr('password'));
                        $oUser = E::ModuleUser()->GetUserById($oUser->getId());

                        // * Сразу авторизуем
                        E::ModuleUser()->Authorization($oUser, false);
                        $this->DropInviteRegister();

                        $this->_addReferal($oUser->getLogin());

                        // * Определяем URL для редиректа после авторизации
                        $sUrl = Config::Get('module.user.redirect_after_registration');
                        if (F::GetRequestStr('return-path')) {
                            $sUrl = F::GetRequestStr('return-path');
                        }
                        E::ModuleViewer()->AssignAjax('sUrlRedirect', $sUrl ? $sUrl : Config::Get('path.root.url'));
                        E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('registration_ok'));
                    }
                } else {
                    E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
                    return;
                }
            } else {
                // * Получаем ошибки
                E::ModuleViewer()->AssignAjax('aErrors', $oUser->_getValidateErrors());
            }
        }
    }
