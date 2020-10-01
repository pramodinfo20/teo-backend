/*
 * Author: Jakub Kotlorz
 * Purpose: To create and populate table responsibility_categories
 */

-- Table: public.responsibility_categories

-- DROP TABLE public.responsibility_categories;

CREATE TABLE public.user_company_structure
(
    id           serial                not null,
    user_id      integer               not null,
    structure_id integer               not null,

    -- check TB-1728 to understand following structure

    -- is leader of company structure (Yes, No, Deputy)
    is_leader    character varying(20) not null default 'No',

    -- is deputy of OTHER organization structure, int id of structure_id, null if not
    is_deputy    integer               null     default null,
    CONSTRAINT user_company_structure_id_pk PRIMARY KEY (id)

)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.user_company_structure
    OWNER TO postgres;

INSERT INTO public.user_company_structure (user_id, structure_id, is_leader, is_deputy)
VALUES (33, 2, 'No', null),
       (50, 2, 'No', null)
