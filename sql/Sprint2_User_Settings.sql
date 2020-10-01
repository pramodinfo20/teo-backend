CREATE TABLE "user_settings"
(
    id         serial  NOT NULL,
    sts_userid integer NOT NULL,
    settings   text    NOT NULL,
    CONSTRAINT c_id PRIMARY KEY (id),
    CONSTRAINT c_sts_userid UNIQUE (sts_userid),
    CONSTRAINT c_sts_userid_fk FOREIGN KEY (sts_userid)
        REFERENCES public.users (id)
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE "user_settings"
    OWNER TO postgres;
