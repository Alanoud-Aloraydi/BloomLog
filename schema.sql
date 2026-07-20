-- ============================================================
--  BloomLog — database schema
--  Import into a fresh MySQL/MariaDB database named `bloomlog`:
--     mysql -u root -p bloomlog < schema.sql
--  or import it through phpMyAdmin.
--
--  Structure matches the original project, with one improvement:
--  a `role` column on `users` for proper admin access control
--  (replacing the old hard-coded admin-email check).
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------
--  users
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `userid`      INT(11)       NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)  NOT NULL,
    `email`       VARCHAR(100)  NOT NULL,
    `password`    VARCHAR(255)  NOT NULL,
    `humidity`    DECIMAL(5,2)  NOT NULL DEFAULT 50.00,
    `city`        VARCHAR(100)  NOT NULL,
    `createdAt`   DATE          NOT NULL,
    `temperature` DECIMAL(5,2)  NOT NULL DEFAULT 25.00,
    `role`        VARCHAR(20)   NOT NULL DEFAULT 'user',
    PRIMARY KEY (`userid`),
    UNIQUE KEY `Email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
--  plantcatalog  (shared catalog, managed by the admin)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `plantcatalog`;
CREATE TABLE `plantcatalog` (
    `plantid`           INT(11)       NOT NULL AUTO_INCREMENT,
    `plantName`         VARCHAR(100)  NOT NULL,
    `wateringfrequency` INT(11)       NOT NULL,
    `image_path`        VARCHAR(255)  NOT NULL,
    `plant_Info`        VARCHAR(500)  NOT NULL,
    `mintemperature`    DECIMAL(5,2)  DEFAULT NULL,
    `maxtemperature`    DECIMAL(5,2)  DEFAULT NULL,
    `minhumidity`       DECIMAL(5,2)  DEFAULT NULL,
    `maxhumidity`       DECIMAL(5,2)  DEFAULT NULL,
    `plant_summary`     VARCHAR(255)  NOT NULL,
    PRIMARY KEY (`plantid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------
--  userplants  (a plant a user added to their own garden)
-- ----------------------------------------------------------
DROP TABLE IF EXISTS `userplants`;
CREATE TABLE `userplants` (
    `user_plant_id`     INT(11)       NOT NULL AUTO_INCREMENT,
    `user_id`           INT(11)       NOT NULL,
    `plant_catalog_id`  INT(11)       NOT NULL,
    `nickname`          VARCHAR(100)  NOT NULL,
    `notes`             VARCHAR(255)  DEFAULT NULL,
    `last_watered_date` DATE          NOT NULL,
    `next_watered_date` DATE          NOT NULL,
    `date_added`        DATE          NOT NULL,
    PRIMARY KEY (`user_plant_id`),
    KEY `user_id` (`user_id`),
    KEY `plant_catalog_id` (`plant_catalog_id`),
    CONSTRAINT `userplants_ibfk_1` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`userid`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `userplants_ibfk_2` FOREIGN KEY (`plant_catalog_id`)
        REFERENCES `plantcatalog` (`plantid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  Seed data — so a fresh install / demo works out of the box
-- ============================================================

-- Accounts (bcrypt-hashed passwords):
--   admin@bloomlog.com / admin123   (role: admin)
--   demo@bloomlog.com  / demo1234   (role: user)
INSERT INTO `users` (`name`, `email`, `password`, `humidity`, `city`, `createdAt`, `temperature`, `role`) VALUES
('Admin', 'admin@bloomlog.com', '$2y$10$Mbq9En0wBYksMgj/Tt1FaOSip7QfeuCsYmDdV2kiq8XEjPU7hSw8e', 50.00, 'Riyadh', CURDATE(), 25.00, 'admin'),
('Demo User', 'demo@bloomlog.com', '$2y$10$2/LtfsxHH9YR6RGsMtBSKOFdx68sRUiAKU784cWeiFywHAdVA2Rbm', 50.00, 'Riyadh', CURDATE(), 25.00, 'user');

-- Starter catalog. Wide ranges so the default 25°C / 50% environment matches.
INSERT INTO `plantcatalog`
    (`plantName`, `wateringfrequency`, `image_path`, `plant_Info`, `mintemperature`, `maxtemperature`, `minhumidity`, `maxhumidity`, `plant_summary`) VALUES
('Snake Plant', 14, 'image/snake.JPG',
 'Snake plants are extremely hardy. Let the soil dry out completely between waterings and place them in indirect light. They tolerate low light and infrequent care.',
 15.00, 35.00, 20.00, 70.00, 'A hardy, low-maintenance plant that purifies the air.'),
('Pothos', 7, 'image/pothos.JPG',
 'Pothos thrives in a wide range of light conditions. Water when the top inch of soil is dry, and trim regularly to keep it bushy.',
 15.00, 32.00, 40.00, 80.00, 'A fast-growing trailing vine, perfect for beginners.');

-- One sample plant in the demo user's garden (due for watering today).
INSERT INTO `userplants`
    (`user_id`, `plant_catalog_id`, `nickname`, `notes`, `last_watered_date`, `next_watered_date`, `date_added`) VALUES
(2, 2, 'Livingroom Pothos', 'Sits on the windowsill', DATE_SUB(CURDATE(), INTERVAL 7 DAY), CURDATE(), DATE_SUB(CURDATE(), INTERVAL 7 DAY));
