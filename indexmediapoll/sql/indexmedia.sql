CREATE TABLE `poll_question` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `title_UNIQUE` (`title` ASC));


CREATE TABLE `poll_answer` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `poll_question_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_poll_question_idx` (`poll_question_id` ASC),
  CONSTRAINT `fk_poll_question`
    FOREIGN KEY (`poll_question_id`)
    REFERENCES `poll_question` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION),
  UNIQUE INDEX `title_UNIQUE` (`title` ASC, `poll_question_id` ASC));

CREATE TABLE `user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `create_date` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `ip_address_UNIQUE` (`ip_address` ASC));

CREATE TABLE `poll_vote` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `poll_answer_id` INT NOT NULL,
  `create_date` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_user_idx` (`user_id` ASC),
  INDEX `fk_poll_answer_idx` (`poll_answer_id` ASC),
  CONSTRAINT `fk_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_poll_answer`
    FOREIGN KEY (`poll_answer_id`)
    REFERENCES `poll_answer` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

insert into poll_question values(null, 'Question1');
insert into poll_answer values(null, 'Option1', 1);
insert into poll_answer values(null, 'Option2', 1);
insert into poll_answer values(null, 'Option3', 1);