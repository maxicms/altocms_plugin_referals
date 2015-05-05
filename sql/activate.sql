SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
CREATE TABLE IF NOT EXISTS `prefix_referals` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `user_id` INT NOT NULL ,
  `referal_id` INT NOT NULL ,
  `date` DATE NOT NULL ,
  UNIQUE (`referal_id`)
) ENGINE=InnoDB ;
