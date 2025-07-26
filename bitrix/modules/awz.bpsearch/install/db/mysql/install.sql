create table if not exists b_awz_bpsearch_bpindex (
    `ID` int(18) NOT NULL AUTO_INCREMENT,
    `APP_ID` varchar(256) NOT NULL,
    `MEMBER_ID` varchar(256) NOT NULL,
    `BPID` int(11) NOT NULL,
    `CRM_MODULE` varchar(256) NOT NULL,
    `CRM_TYPE` varchar(256) NOT NULL,
    `CRM_DOCUMENT` varchar(256) NOT NULL,
    `ACT_NAME` varchar(256) NOT NULL,
    `ACT_ID` varchar(256) NOT NULL,
    `CODE` varchar(256) NOT NULL,
    `DATE_UP` datetime NOT NULL,
    `PARAMS` longtext NOT NULL,
    PRIMARY KEY (`ID`),
    index IX_APP (APP_ID, MEMBER_ID),
    index IX_APP_BPID (APP_ID, MEMBER_ID, BPID)
);

create table if not exists b_awz_bpsearch_names (
    `ID` int(18) NOT NULL AUTO_INCREMENT,
    `APP_ID` varchar(256) NOT NULL,
    `MEMBER_ID` varchar(256) NOT NULL,
    `ACT_TITLE` varchar(256) NOT NULL,
    `ACT_NAME` varchar(256) NOT NULL,
    PRIMARY KEY (`ID`),
    index IX_ACT_APP_NAME (APP_ID, MEMBER_ID, ACT_NAME)
);
