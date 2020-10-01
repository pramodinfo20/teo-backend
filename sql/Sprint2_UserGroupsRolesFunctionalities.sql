/*
 * Author: Jakub Kotlorz
 * Purpose: To create and populate table public.user_roles
 */

-- Table: public.user_roles

-- DROP TABLE public.user_roles;

CREATE SEQUENCE user_roles_id_seq;
CREATE TABLE public.user_roles
(
    id   integer                NOT NULL DEFAULT nextval('user_roles_id_seq'::regclass),
    name character varying(255) NOT NULL,
    CONSTRAINT user_roles_pkey PRIMARY KEY (id)
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.user_roles
    OWNER TO postgres;
ALTER SEQUENCE user_roles_id_seq OWNED BY public.user_roles.id;

INSERT INTO public.user_roles (name)
VALUES ('Aftersales'),
       ('Engineering'),
       ('QM'),
       ('QS'),
       ('Zentrale'),
       ('Fuhrparksteuerung'),
       ('Fuhrparkverwaltung'),
       ('PPS'),
       ('Charging Infrastruktur: EBG Compleo'),
       ('Charging Infrastruktur: AixACCT'),
       ('Charging Infrastruktur: Innogy'),
       ('DB History'),
       ('Post Fleet'),
       ('Werkstätte'),
       ('Hotline');

/*
 * Author: Jakub Kotlorz
 * Purpose: To create and populate table sts_organization_structure
 */

-- Table: public.sts_organization_structure

-- DROP TABLE public.sts_organization_structure;

CREATE SEQUENCE sts_organization_structure_id_seq;
CREATE TABLE public.sts_organization_structure
(
    id         integer                NOT NULL DEFAULT nextval('sts_organization_structure_id_seq'::regclass),
    name       character varying(255) NOT NULL,
    parent_id  integer                NOT NULL DEFAULT 0,
    costcenter integer
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.sts_organization_structure
    OWNER TO postgres;
ALTER SEQUENCE sts_organization_structure_id_seq OWNED BY public.sts_organization_structure.id;

INSERT INTO public.sts_organization_structure (name, parent_id, costcenter)
VALUES ('CTO', 0, NULL),
       ('E/E', 1, 512),
       ('system engineering', 2, 511),
       ('DAC', 2, 513),
       ('DIA', 4, 513),
       ('Gesamtfahrzeug', 1, 540),
       ('Testing & Homologation', 6, 563),
       ('CEO', 0, NULL),
       ('Production', 8, NULL),
       ('Field Support', 9, NULL),
       ('Betrieb', 8, NULL),
       ('Aftersales', 11, NULL),
       ('QM', 9, NULL)
;

/*
 * Author: Jakub Kotlorz
 * Purpose: To create and populate table user_role_company_structure
 */

-- Table: public.user_role_company_structure

-- DROP TABLE public.user_role_company_structure;

CREATE TABLE public.user_role_company_structure
(
    user_role_id                  integer NOT NULL,
    sts_organization_structure_id integer NOT NULL
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.user_role_company_structure
    OWNER TO postgres;

INSERT INTO public.user_role_company_structure (user_role_id, sts_organization_structure_id)
VALUES (1, 12),
       (1, 9),
       (3, 13),
       (2, 1),
       (2, 2),
       (2, 3),
       (2, 4),
       (2, 5),
       (2, 6),
       (2, 7)
;


/*
 * Author: Jakub Kotlorz
 * Purpose: To create and populate table functionality_groups
 */

-- Table: public.functionality_groups

-- DROP TABLE public.functionality_groups;

CREATE SEQUENCE functionality_groups_id_seq;
CREATE TABLE public.functionality_groups
(
    id               integer                NOT NULL DEFAULT nextval('functionality_groups_id_seq'::regclass),
    name             character varying(255) NOT NULL,
    description      character varying(255),
    always_read_only boolean                         DEFAULT false,
    always_write     boolean                         DEFAULT false
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.functionality_groups
    OWNER TO postgres;

ALTER SEQUENCE functionality_groups_id_seq OWNED BY public.functionality_groups.id;

INSERT INTO public.functionality_groups (name, description, always_read_only, always_write)
VALUES ('vehicle data (from vehicle)', NULL, true, false),
       ('vehicle data2', NULL, false, false),
       ('vehicle configuration', NULL, false, false),
       ('car approval', NULL, false, false)
;


/*
 * Author: Jakub Kotlorz
 * Purpose: To create and populate table functionality_group_user_role
 */

-- Table: public.functionality_group_user_role

-- DROP TABLE public.functionality_group_user_role;

CREATE SEQUENCE functionality_group_user_role_id_seq;
CREATE TABLE public.functionality_group_user_role
(
    id                     integer NOT NULL DEFAULT nextval('functionality_group_user_role_id_seq'::regclass),
    functionality_group_id integer NOT NULL,
    user_role_id           integer NOT NULL,
    write_permissions      boolean          DEFAULT false
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.functionality_group_user_role
    OWNER TO postgres;

ALTER SEQUENCE functionality_group_user_role_id_seq OWNED BY public.functionality_group_user_role.id;

INSERT INTO public.functionality_group_user_role (functionality_group_id, user_role_id, write_permissions)
VALUES (1, 1, false),
       (1, 2, false),
       (1, 3, false),
       (1, 4, false),
       (2, 1, false),
       (2, 2, true),
       (2, 3, false),
       (2, 4, false),
       (3, 1, false),
       (3, 2, true),
       (3, 3, false),
       (3, 4, true),
       (4, 4, true),
       (4, 2, false)
;
