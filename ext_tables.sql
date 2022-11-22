#
# Add SQL definition of database tables
#
# KU table for RSS feed url
CREATE TABLE tt_content (
    ku_recent_content_backend_module varchar(512) DEFAULT '' NOT NULL,
    ku_rss_items int(100) DEFAULT 10 NOT NULL,
    ku_rss_layout varchar(255) DEFAULT '' NOT NULL
);