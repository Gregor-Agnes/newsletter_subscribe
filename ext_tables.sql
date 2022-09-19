#
# Table structure for table 'tt_address'
#
#
CREATE TABLE tt_address
(
    data_protection_accepted    tinyint(1) unsigned DEFAULT '0'  NOT NULL,
    subscription_hash           varchar(255)        DEFAULT Null NULL,
    last_hit                    int(11)             DEFAULT '0'  NOT NULL,
    hit_number                  int(11)             DEFAULT '0'  NOT NULL,
    module_sys_dmail_newsletter tinyint(3) unsigned DEFAULT '0'  NOT NULL,
    module_sys_dmail_category   int(10) unsigned    DEFAULT '0'  NOT NULL,
    module_sys_dmail_html       tinyint(3) unsigned DEFAULT '0'  NOT NULL,
    salutation                  varchar(255)        DEFAULT Null NULL
);


