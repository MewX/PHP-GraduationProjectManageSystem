-- 创建数据库
create database GP;
use GP;


-- 创建表
create table account (
	id bigint not null primary key,
    pwd varchar(256) default 'NmI4NmIyNzNmZjM0ZmNlMTlkNmI4MDRlZmY1YTNmNTc0N2FkYTRlYWEyMmYxZDQ5YzAxZTUyZGRiNzg3NWI0Yg==', -- sha256('1')
    name varchar(20) not null,
    type char(1) default 'S' check (sex in ('S','T')) -- student or teacher
);

create table task (
	id int not null primary key,
    name varchar (50) not null,
	content text(65535),
    capacity int not null default 1,
    holder bigint not null,
    type int default 1, -- course type
    
    constraint fk_task_holder foreign key (holder) references account(id)
);

create table file (
	id int not null primary key,
    url varchar(1024),
    name varchar(1024),
    
    accid bigint not null,
    
    constraint fk_file_accid foreign key (accid) references account(id)
);

create table choice (
	accid bigint not null,
    tskid int not null,
    file00 int, -- 任务书及模板（老师上传）
    file01 int, -- 开题报告（学生上传）
    file02 int, -- 毕业论文（学生上传）
    file03 int, -- 评价表（教师上传）
    file04 int, -- 答辩提问录（教师上传）
    
    constraint pk_choice_acctsk primary key (accid, tskid),
    constraint fk_choice_accid foreign key (accid) references account(id),
    constraint fk_choice_tskid foreign key (tskid) references task(id),
    constraint fk_choice_file00 foreign key (file00) references file(id),
    constraint fk_choice_file01 foreign key (file01) references file(id),
    constraint fk_choice_file02 foreign key (file02) references file(id),
    constraint fk_choice_file03 foreign key (file03) references file(id),
    constraint fk_choice_file04 foreign key (file04) references file(id)
);

create table notification (
	notid bigint not null primary key,
    title varchar(256) not null,
    message text(1024) not null,
    sender bigint,
    receiver bigint not null,
    hasread char(1) default 'N' check (hasread in ('Y','N')), -- has read or not, Yes or No
    
    
    constraint fk_notification_sender foreign key (sender) references account(id),
    constraint fk_notification_receiver foreign key (receiver) references account(id)
);


-- 录入数据
insert into account(id, name, type) values (2012001, '学生1', 'S');
insert into account(id, name, type) values (2012002, '学生2', 'S');
insert into account(id, name, type) values (2012003, '学生3', 'S');
insert into account(id, name, type) values (2012004, '学生4', 'S');
insert into account(id, name, type) values (992012001, '教师1', 'T');
insert into account(id, name, type) values (992012002, '教师2', 'T');

insert into task(id, name, content, capacity, holder) values (1001, '百度知道网络互动问答平台研究', '全球最大中文互动问答社区百度知道进行了首页的全新改版。此次改版在页面设计、产品布局、用户互动等多方面进行了优化调整。改版后，百度知道强化了其一贯专业、优质的产品特点，在获取答案和分享知识方面，将给广大用户带来全新的使用体验。不仅如此，百度知道作为知识分享平台，此次特别加大了对回答用户的展现，同时也加速了知识的流动，让群体的智慧得到了最大化的激发和利用。', 2, 992012001);
insert into task(id, name, content, capacity, holder) values (1002, '电子商务中议价系统的实现', '对于每一个淘宝客服来说最头痛的莫过于客户砍价了，砍价的原因多种多样，有的是喜欢便宜，有的是养成了习惯……我吧我个人所遇到的不同砍价类型分为以下几点：亲们是否也遇到过累世的问题呢？相信每位客服都有遇到过吧！！！', 4, 992012001);
insert into task(id, name, content, capacity, holder) values (1003, '餐饮点餐结算管理系统', '随着中国市场经济的飞速发展和人民生活水平的不断提高，人们对餐饮业的要求也随着提高,餐饮业的竞争越来越激烈，传统的人工点餐方式效率低，容易出错，已不能满足目前客人的需要。各个餐饮酒店想在竞争中取得优势，就必须在经营管理、产品服务等方面提高质量,因此餐饮管理显得尤为重要。面对庞大的信息量，就需要一个自助点餐管理系统来提高餐饮管理效率。', 2, 992012002);
insert into task(id, name, content, capacity, holder) values (1004, '停车场车位预定计费管理系统', '停车场车位管理与收费系统是伴随着公用收费停车场这一新生事物而诞生的。它的出现克服了原始的人工收费方式存在的收费过程繁琐，通行效率低下以及票款流失等难以解决的问题。随着经济的发展以及技术的进步，种类繁多的停车场管理系统竞相出现。许多现代控制领域及智能交通领域的前沿技术在停车场管理系统中得到广泛应用，使当今停车场管理系统越来越具有智能化的特点。', 6, 992012002);

insert into file(id, url, name, accid) values (1, 'http://prog-prog.stor.sinaapp.com/1.docx', '毕业设计模板.docx', 992012001);
insert into file(id, url, name, accid) values (2, 'http://prog-prog.stor.sinaapp.com/2.docx', '评价表.docx', 992012001);
insert into file(id, url, name, accid) values (3, 'http://prog-prog.stor.sinaapp.com/2.docx', '答辩提问录.docx', 992012001);
insert into file(id, url, name, accid) values (4, 'http://prog-prog.stor.sinaapp.com/2.docx', '开题报告.docx', 2012001);
insert into file(id, url, name, accid) values (5, 'http://prog-prog.stor.sinaapp.com/2.docx', '毕业论文.docx', 2012001);
insert into file(id, url, name, accid) values (6, 'http://prog-prog.stor.sinaapp.com/2.docx', '开题报告.docx', 2012003);
insert into file(id, url, name, accid) values (7, 'http://prog-prog.stor.sinaapp.com/2.docx', '毕业论文.docx', 2012003);

insert into choice(accid, tskid, file00, file01, file02, file03, file04) values ('2012001', '1001', 1, 4, 5, 2, 3);
insert into choice(accid, tskid, file00, file03, file04) values ('2012002', '1001', 1, 2, 3);
insert into choice(accid, tskid, file00, file01, file02, file03, file04) values ('2012003', '1002', 1, 6, 7, 2, 3);


-- 删除数据表
drop table notification;
drop table choice;
drop table file;
drop table task;
drop table account;
