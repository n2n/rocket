DROP TABLE IF EXISTS `rocket_user`;
CREATE TABLE `rocket_user` (
                               `id` INT NOT NULL AUTO_INCREMENT,
                               `nick` VARCHAR(255) NOT NULL,
                               `firstname` VARCHAR(255) NULL DEFAULT NULL,
                               `lastname` VARCHAR(255) NULL DEFAULT NULL,
                               `email` VARCHAR(255) NULL DEFAULT NULL,
                               `power` ENUM('superadmin','admin','none') NOT NULL DEFAULT 'none',
                               `password` VARCHAR(255) NOT NULL,
                               PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;
ALTER TABLE `rocket_user` ADD UNIQUE INDEX `nick` (`nick`);

DROP TABLE IF EXISTS `rocket_user_rocket_user_groups`;
CREATE TABLE `rocket_user_rocket_user_groups` (
                                                  `rocket_user_id` INT NOT NULL,
                                                  `rocket_user_group_id` INT NOT NULL,
                                                  PRIMARY KEY (`rocket_user_id`, `rocket_user_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci ;

DROP TABLE IF EXISTS `sort_test_obj`;
CREATE TABLE `sort_test_obj` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `holeradio` VARCHAR(255) NULL DEFAULT NULL,
    `num` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci ;

DROP TABLE IF EXISTS `quick_search_test_obj`;
CREATE TABLE `quick_search_test_obj` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `holeradio` VARCHAR(255) NULL DEFAULT NULL,
     `holeradio2` VARCHAR(255) NULL DEFAULT NULL,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci ;




DROP TABLE IF EXISTS `integrated_src_test_obj`;
CREATE TABLE `integrated_src_test_obj` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `holeradio` VARCHAR(255) NULL DEFAULT NULL,
     `target_test_obj_id` INT NOT NULL,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci ;

DROP TABLE IF EXISTS `integrated_target_test_obj`;
CREATE TABLE `integrated_target_test_obj` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `dingsel` VARCHAR(255) NULL DEFAULT NULL,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci ;


DROP TABLE IF EXISTS `enum_test_obj`;
CREATE TABLE `enum_test_obj` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `auto_detected_prop` VARCHAR(255) NULL DEFAULT NULL,
     `annotated_prop` VARCHAR(255) NULL DEFAULT NULL,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci ;


DROP TABLE IF EXISTS `translatable_test_obj`;
CREATE TABLE `translatable_test_obj` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `some_label` VARCHAR(255) NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci ;

DROP TABLE IF EXISTS `translation_test_obj`;
CREATE TABLE `translation_test_obj` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `n2n_locale` VARCHAR(255) NOT NULL,
   `name` VARCHAR(255) NULL DEFAULT NULL,
   `translatable_test_obj_id` INT NOT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci ;


DROP TABLE IF EXISTS `string_test_obj`;
CREATE TABLE `string_test_obj` (
                                        `id` INT NOT NULL AUTO_INCREMENT,
                                        `holeradio` VARCHAR(255) NULL DEFAULT NULL,
                                        `mandatory_holeradio` VARCHAR(255) NULL DEFAULT NULL,
                                        `anno_holeradio` VARCHAR(255) NULL DEFAULT NULL,
                                        `holeradio_obj` VARCHAR(255) NULL DEFAULT NULL,
                                        `mandatory_holeradio_obj` VARCHAR(255) NULL DEFAULT NULL,
                                        `anno_holeradio_obj` VARCHAR(255) NULL DEFAULT NULL,
                                        PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci ;