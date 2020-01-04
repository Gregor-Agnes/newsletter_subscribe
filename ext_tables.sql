#
# Table structure for table 'tx_store_domain_model_article'
#
#
CREATE TABLE tt_address
(
    data_protection_accepted  tinyint(1) unsigned DEFAULT '0' NOT NULL,
    subscription_hash           varchar(255) DEFAULT Null NULL,
    module_sys_dmail_newsletter tinyint(3) unsigned DEFAULT '0' NOT NULL,
    module_sys_dmail_category int(10) unsigned    DEFAULT '0' NOT NULL,
    module_sys_dmail_html     tinyint(3) unsigned DEFAULT '0' NOT NULL
);


