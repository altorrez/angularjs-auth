CREATE TABLE users (
	`id` INT(11) AUTO_INCREMENT,
    `username` VARCHAR(30) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(120),
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE (`username`)
);

CREATE TABLE sessions (
	`session` VARCHAR(128) NOT NULL,
    `userid` INT(11) NOT NULL,
    `expire` TIMESTAMP NOT NULL,
    PRIMARY KEY (`session`),
    FOREIGN KEY (`userid`) REFERENCES `users`(`id`) ON DELETE CASCADE
);