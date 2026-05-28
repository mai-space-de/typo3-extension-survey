CREATE TABLE tx_maisurvey_survey (
    title varchar(255) DEFAULT '' NOT NULL,
    description longtext,
    is_active smallint unsigned DEFAULT '0' NOT NULL,
    allow_anonymous smallint unsigned DEFAULT '1' NOT NULL,
    prevent_duplicates smallint unsigned DEFAULT '1' NOT NULL,
    success_message longtext
);

CREATE TABLE tx_maisurvey_question (
    survey int unsigned DEFAULT '0' NOT NULL,
    question varchar(255) DEFAULT '' NOT NULL,
    type varchar(255) DEFAULT 'single' NOT NULL,
    required smallint unsigned DEFAULT '0' NOT NULL,
    options json,
    scale_min int unsigned DEFAULT '1' NOT NULL,
    scale_max int unsigned DEFAULT '5' NOT NULL
);

CREATE TABLE tx_maisurvey_submission (
    survey int unsigned DEFAULT '0' NOT NULL,
    session_hash varchar(255) DEFAULT '' NOT NULL,
    fe_user_uid int unsigned DEFAULT '0' NOT NULL,
    submitted_at bigint DEFAULT '0' NOT NULL
);

CREATE TABLE tx_maisurvey_answer (
    submission int unsigned DEFAULT '0' NOT NULL,
    question int unsigned DEFAULT '0' NOT NULL,
    value longtext
);
