/*
 * Author: Jakub Kotlorz
 * Purpose: To store conversion table
 */

-- Table: public.conversion_sets

-- DROP TABLE public.conversion_sets;

CREATE SEQUENCE conversion_sets_id_seq;
CREATE TABLE public.conversion_sets
(
    id     integer NOT NULL DEFAULT nextval('conversion_sets_id_seq'::regclass),
    frozen boolean          DEFAULT FALSE
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.conversion_sets
    OWNER TO postgres;
ALTER SEQUENCE conversion_sets_id_seq OWNED BY public.conversion_sets.id;

INSERT INTO public.conversion_sets (frozen)
VALUES (false),
       (true),
       (false),
       (false),
       (false),
       (false);


-- Table: public.conversion_characters

-- DROP TABLE public.conversion_characters;

CREATE SEQUENCE conversion_characters_id_seq;
CREATE TABLE public.conversion_characters
(
    id               integer      NOT NULL DEFAULT nextval('conversion_characters_id_seq'::regclass),
    conversion_set   integer      NOT NULL,
    conversion_key   smallint     NOT NULL,
    conversion_value character(1) NOT NULL
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.conversion_characters
    OWNER TO postgres;
ALTER SEQUENCE conversion_characters_id_seq OWNED BY public.conversion_characters.id;

INSERT INTO public.conversion_characters (conversion_set, conversion_key, conversion_value)
VALUES (2, 128, 'À'),
       (2, 201, 'Á'),
       (2, 205, 'Â'),
       (2, 210, 'Ã'),
       (2, 220, 'Ä'),
       (2, 230, 'Å'),
       (2, 231, 'È'),
       (2, 255, 'Ê'),
       (3, 129, 'þ'),
       (4, 200, 'ý'),
       (3, 201, 'ø'),
       (3, 202, 'ã'),
       (3, 130, 'Û'),
       (3, 131, '‰'),
       (3, 255, 'œ'),
       (3, 254, 'Ÿ');
