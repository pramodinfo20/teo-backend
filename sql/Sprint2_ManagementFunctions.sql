/*
 * Author: Jakub Kotlorz
 * Purpose: To create and populate table management_functions
 */

-- Table: public.management_functions

-- DROP TABLE public.management_functions;

CREATE SEQUENCE management_functions_id_seq;
CREATE TABLE public.management_functions
(
    id   integer                NOT NULL DEFAULT nextval('management_functions_id_seq'::regclass),
    name character varying(100) NOT NULL
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.management_functions
    OWNER TO postgres;
ALTER SEQUENCE management_functions_id_seq OWNED BY public.management_functions.id;

INSERT INTO public.management_functions (name)
VALUES ('vehicles configuration'),
       ('CoC parameters'),
       ('global parameters'),
       ('ECU properties'),
       ('SW upload'),
       ('diagnostic software parameters')
;


/*
 * Author: Jakub Kotlorz
 * Purpose: To create and populate table management_functions_users
 */

-- Table: public.management_functions_users

-- DROP TABLE public.management_functions_users;

CREATE SEQUENCE management_functions_users_id_seq;
CREATE TABLE public.management_functions_users
(
    id                integer NOT NULL      DEFAULT nextval('management_functions_users_id_seq'::regclass),
    function_id       integer NOT NULL,
    user_id           integer NOT NULL,
    is_structure      boolean               DEFAULT false,
    structure_details character varying(20) DEFAULT NULL
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.management_functions_users
    OWNER TO postgres;
ALTER SEQUENCE management_functions_users_id_seq OWNED BY public.management_functions_users.id;

INSERT INTO public.management_functions_users (is_structure, user_id, function_id, structure_details)
VALUES (false, 50, 1, null),
       (false, 50, 2, null),
       (true, 5, 5, 'leader'),
       (true, 5, 6, 'all'),
       (false, 50, 5, null),
       (false, 50, 6, null),
       (true, 4, 5, 'deputy')
;
