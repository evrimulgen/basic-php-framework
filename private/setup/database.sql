
create table user
(
	id int(32) not null auto_increment,
	firstname varchar(50),
    lastname varchar(50),
    username varchar(50),
	email varchar(50) not null,
	password varchar(255) not null,
	validation_code varchar(32),
	reset_pw_token varchar(32),
	primary key(id)
);

create table meta
(
	name varchar(50) not null,
	value text,
	primary key(name)
);
