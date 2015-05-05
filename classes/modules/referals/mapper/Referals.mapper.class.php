<?php

    class PluginReferals_ModuleReferals_MapperReferals extends Mapper
    {
        public function addReferal($user_id, $referal_id, $date)
        {
            $sql = "INSERT INTO ?_referals (`user_id`, `referal_id`, `date`) VALUES (?d, ?d, ?)";
            if ($result = $this->oDb->select($sql, $user_id, $referal_id, $date)) {
                return true;
            }
            return false;
        }

        public function getReferalsCount($user_id)
        {
            $sql = "SELECT COUNT(referal_id) FROM ?_referals WHERE 	user_id = ?d";
            if ($result = $this->oDb->select($sql, $user_id)) {
                return $result;
            }
            return false;
        }

        public function getReferals($user_id)
        {
            $mapper = E::GetMapper('ModuleUser');
            $sql    = "SELECT user_id, referal_id FROM ?_referals WHERE user_id = ?d";

            if ($result = $this->oDb->select($sql, $user_id)) {
                $referals = array();
                foreach ($result as $res) {
                    $referals[] = $res['referal_id'];
                }
                $return[] = $mapper->GetUsersByArrayId($referals);
                $referals = array();
                foreach ($return[0] as $r) {
                    $referals[] = $r;
                }
                return $referals;
            }

            return false;
        }
    }
