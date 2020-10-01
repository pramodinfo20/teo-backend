CREATE TABLE public.responsibility_categories
(
    id        serial                 NOT NULL,
    parent_id integer,
    name      character varying(255) NOT NULL,
    code      character varying(255),
    CONSTRAINT responsibility_categories_id_pk PRIMARY KEY (id)
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.responsibility_categories
    OWNER TO postgres;

INSERT INTO public.responsibility_categories (parent_id, name, code)
VALUES (0, 'ECUs', 'ecus'),
       (0, 'Model ranges', 'model_ranges'),
       (0, 'Global parameters', 'global_parameters'),
       (0, 'TEO parameters', null),
       (0, 'CoC parameters', null),
       (0, 'ODX file view', null),
       (0, 'Charging control', 'charging_paramter'),
       (0, 'Vehicle report paper', null),
       (0, 'Deviation permissions', null),
       (0, 'Special permission', null),
       (0, 'CAN bus file master', null),
       (0, 'CoC paper signatories ', null),
       (0, 'Removing CoC papers', null),
       (2, 'D-series', null),
       (2, 'E-series', null),
       (1, 'BCM', null),
       (1, 'BMS', null),
       (1, 'CDIS', null),
       (3, 'maximum engine power', null),
       (3, 'EXP functionality', null),
       (7, 'maximum charger power', null),
       (7, 'precondition duration', null)
;


/*
 * Author: Jakub Kotlorz
 * Purpose: To create and populate table responsibility_assignments
 */

-- Table: public.responsibility_assignments

-- DROP TABLE public.responsibility_assignments;

CREATE TABLE public.responsibility_assignments
(
    id                   serial  NOT NULL,
    assigned_category_id integer NOT NULL,
    assigned_user_id     integer NOT NULL,
    is_structure         boolean               DEFAULT false,
    structure_details    character varying(20) DEFAULT NULL,
    is_responsible       boolean               default false,
    is_deputy            boolean               default false,
    CONSTRAINT responsibility_assignments_id_pk PRIMARY KEY (id)
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.responsibility_assignments
    OWNER TO postgres;

INSERT INTO public.responsibility_assignments (assigned_user_id, assigned_category_id, is_responsible, is_deputy,
                                               is_structure, structure_details)
VALUES (5, 17, true, false, false, null),
       (6, 17, false, false, false, null),
       (7, 17, false, false, false, null),
       (32, 17, false, true, false, null),
       (35, 19, false, true, false, null),
       (14, 19, true, false, false, null),
       (8, 19, false, false, false, null),
       (13, 8, false, false, false, null),
       (15, 8, true, false, false, null),
       (3, 8, false, true, false, null)
;
