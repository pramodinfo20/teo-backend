/*
 * Author: Jakub Kotlorz
 * Purpose: To store history of changes
 */

-- Table: public.change_log_history

-- DROP TABLE public.change_log_history;

CREATE SEQUENCE change_log_history_id_seq;
CREATE TABLE public.change_log_history
(
    id           integer                NOT NULL DEFAULT nextval('change_log_history_id_seq'::regclass),
    user_id      integer                NOT NULL,
    posting_date TIMESTAMP              NOT NULL DEFAULT CURRENT_TIMESTAMP,
    context      character varying(128) NOT NULL,
    description  character varying(255) NOT NULL
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.change_log_history
    OWNER TO postgres;
ALTER SEQUENCE change_log_history_id_seq OWNED BY public.change_log_history.id;
