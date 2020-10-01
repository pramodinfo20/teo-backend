-- Table: public.persons_list_hr

-- DROP TABLE public.persons_list_hr;

CREATE SEQUENCE persons_list_hr_id_seq;
CREATE TABLE public.persons_list_hr
(
    id                     integer                NOT NULL DEFAULT nextval('persons_list_hr_id_seq'::regclass),
    upload_id              integer                NOT NULL,
    person                 character(64)          NOT NULL,
    organization_id        integer                NOT NULL,
    business_unit          character varying(255) NOT NULL,
    kind                   character varying(255) NOT NULL,
    is_leader              boolean                         DEFAULT false,
    deputy_organization_id integer                         default NULL
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.persons_list_hr
    OWNER TO postgres;
ALTER SEQUENCE persons_list_hr_id_seq OWNED BY public.persons_list_hr.id;
