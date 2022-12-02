DROP TABLE IF EXISTS `sort_test_obj`;
CREATE TABLE `sort_test_obj` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `holeradio` VARCHAR(255) NULL DEFAULT NULL,
    `num` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci ;

DROP TABLE IF EXISTS `quick_search_test_obj`;
CREATE TABLE `sort_test_obj` (
     `id` INT NOT NULL AUTO_INCREMENT,
     `holeradio` VARCHAR(255) NULL DEFAULT NULL,
     `holeradio2` VARCHAR(255) NULL DEFAULT NULL,
     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci ;