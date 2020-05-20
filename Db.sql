create database drinker;

use drinker;

CREATE TABLE users (
 iduser int(11) NOT NULL auto_increment,
 name varchar(255) NOT NULL,
 email varchar(255) NOT NULL,
 password varchar(255) NOT NULL,
 PRIMARY KEY (iduser)
);

CREATE TABLE drinks(
	iddrink int(11) NOT NULL auto_increment,
    iduser int(11) NOT NULL,
    amount int(11) NOT NULL,
    request datetime NOT NULL,
    situation boolean NOT NULL,
    PRIMARY KEY (iddrink),
    CONSTRAINT fk_user FOREIGN KEY (iduser) REFERENCES users (iduser)
);

CREATE TABLE drinktotal(
	iddrinktotal int(11) NOT NULL auto_increment,
    iduser int(11) NOT NULL,
    total int(11) NOT NULL,
    calls int(11) NOT NULL,
    PRIMARY KEY (iddrinktotal),
    CONSTRAINT fk_userdk FOREIGN KEY (iduser) REFERENCES users (iduser)
);

USE `drinker`;
DROP procedure IF EXISTS `sp_users_create`;

DELIMITER $$
USE `drinker`$$
CREATE PROCEDURE `sp_users_create` (
            namep varchar(100),
            passwordp varchar(255),
            emailp varchar(100)
            )
BEGIN
	declare resultA int unsigned default 0;
    DECLARE viduser INT;
    
	set resultA = (SELECT COUNT(*) FROM users WHERE name = namep and email = emailp);

            if resultA = 0 then
              insert into users
              values(0,namep,emailp,passwordp);
              
              SET viduser = LAST_INSERT_ID();
              
              insert into drinktotal values(0,viduser,0,0);
              
           end if;
END$$

DELIMITER ;

USE `drinker`;
DROP procedure IF EXISTS `sp_drink_create`;

DELIMITER $$
USE `drinker`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_drink_create`(
	iduserp int,
    amountp int
)
BEGIN
	DECLARE totaldrinkp INT;
    
	insert into drinks
              values(0,iduserp,amountp,NOW(),1);
              
    update drinktotal set total = (select sum(amount) as totals  from drinks where iduser = iduserp and situation = 1), calls = calls+1 where iduser = iduserp;

END$$

DELIMITER ;

USE `drinker`;
DROP procedure IF EXISTS `sp_users_delete`;

DELIMITER $$
USE `drinker`$$
CREATE PROCEDURE `sp_users_delete` (id int)
BEGIN
	
    delete from drinktotal where iduser = id;
    
    delete from drinks where iduser = id;
    
    delete from users where iduser = id;

END$$

DELIMITER ;

