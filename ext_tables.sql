CREATE TABLE sys_file_reference (
    caption   text default null,
    copyright text default null,

    KEY refs (uid_foreign,tablenames,fieldname,hidden,deleted,t3ver_wsid,t3ver_oid,t3ver_state)
);

CREATE TABLE pages (
    KEY rootline (uid,t3ver_wsid,deleted),
    KEY menu (pid,sys_language_uid,deleted,t3ver_state,t3ver_wsid,t3ver_oid,t3ver_state,hidden,starttime,endtime,doktype,fe_group)
);

CREATE TABLE sys_file_metadata (
    caption text default null
);

CREATE TABLE tt_content (
    tx_theme_base_link                              varchar(1024)       DEFAULT ''  NOT NULL,
    tx_theme_base_link_label                        tinytext,
    tx_theme_base_link_1                            varchar(1024)       DEFAULT ''  NOT NULL,
    tx_theme_base_link_label_1                      tinytext,
);
