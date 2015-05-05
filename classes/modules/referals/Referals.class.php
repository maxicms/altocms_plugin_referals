<?php

    class PluginReferals_ModuleReferals extends Module
    {

        protected $user;
        protected $mapper;

        public function Init()
        {
            $this->mapper = Engine::getMapper(__CLASS__);
            $this->user   = $this->User_getUserCurrent();
        }

        public function addReferal($user_id, $referal_id, $date)
        {
            return $this->mapper->addReferal($user_id, $referal_id, $date);
        }

        public function getReferalsCount($user_id)
        {
            return $this->mapper->getReferalsCount($user_id);
        }

        public function getReferals($user_id)
        {
            return $this->mapper->getReferals($user_id);
        }

        public function sendPM($id, $title, $message)
        {
            $talk = new ModuleTalk_EntityTalk();
            $talk->setUserId($id);
            $talk->setTitle($title);
            $text = $this->Text_Parser($this->Text_Parser($message));
            $talk->setText($text);
            $talk->setDate(date("Y-m-d H:i:s"));
            $talk->setDateLast(date("Y-m-d H:i:s"));
            $talk->setUserIp(func_getIp());

            if ($talk = $this->Talk_AddTalk($talk)) {
                $talk_id   = $talk->getId();
                $talk_user = new ModuleTalk_EntityTalkUser();
                $talk_user->setTalkId($talk_id);
                $talk_user->setUserId($id);
                $talk_user->setDateLast(null);
                $this->Talk_AddTalkUser($talk_user);
                return true;
            }
            return false;
        }
    }
