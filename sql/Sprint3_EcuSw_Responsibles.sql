/*
 * Author: Jakub Kotlorz
 * Purpose: To create and populate table responsibility_ecus
 */

-- Table: public.responsibility_ecus

-- DROP TABLE public.responsibility_ecus;

CREATE TABLE public.responsibility_ecus
(
    resp_cat_id integer not null,
    ecu_id      integer not null,
    CONSTRAINT responsibility_ecus_resp_cat_id_pk PRIMARY KEY (resp_cat_id),
    CONSTRAINT responsibility_ecus_fk FOREIGN KEY (resp_cat_id)
        REFERENCES public.responsibility_categories (id)

)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.responsibility_ecus
    OWNER TO postgres;

INSERT INTO public.responsibility_ecus (resp_cat_id, ecu_id)
VALUES (16, 2),
       (17, 3),
       (18, 6)
;
