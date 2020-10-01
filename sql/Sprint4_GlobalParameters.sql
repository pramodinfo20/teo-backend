/*
 * Author: Grzegorz Stolarz
 * Purpose: To create table ecu_global_parameters
 */

-- Table: public.ecu_global_parameters

-- DROP TABLE public.ecu_global_parameters;
CREATE SEQUENCE ecu_global_parameters_id_seq;
CREATE TABLE public.ecu_global_parameters
(
    ecu_global_parameter_id integer not null default nextval('ecu_global_parameters_id_seq'::regclass),
    ecu_parameter_set_id    integer,
    value                   char;
min_value character varying(128) NOT NULL,
  max_value character varying(128) NOT NULL,
  is_super_global boolean default false,
  responsible_person integer,
  CONSTRAINT ecu_global_parameters_pkey PRIMARY KEY (ecu_global_parameter_id),
  CONSTRAINT ecu_global_parameters_ecu_parameter_set_id_fkey FOREIGN KEY (ecu_parameter_set_id)
      REFERENCES public.ecu_parameter_sets (ecu_parameter_set_id) MATCH SIMPLE
      ON
UPDATE NO ACTION
ON
DELETE NO ACTION
)
WITH (
         OIDS= FALSE
     );
ALTER TABLE public.ecu_global_parameters
    OWNER TO postgres;
GRANT ALL ON TABLE public.ecu_global_parameters TO postgres;
GRANT ALL ON TABLE public.ecu_global_parameters TO leitwarte;

-- Table: public.ecu_global_parameters_values

-- DROP TABLE public.ecu_global_parameters_values;
CREATE SEQUENCE ecu_global_parameters_values_id_seq;
CREATE TABLE public.ecu_global_parameters_values
(
    ecu_global_parameter_value_id integer                not null default nextval('ecu_global_parameters_values_id_seq'::regclass),
    ecu_global_parameter_id       integer                not null,
    value                         character varying(128) not null,
    CONSTRAINT ecu_global_parameters_values_pkey PRIMARY KEY (ecu_global_parameter_value_id),
    CONSTRAINT ecu_global_parameters_values_ecu_global_parameter_id_fkey FOREIGN KEY (ecu_global_parameter_id)
        REFERENCES public.ecu_global_parameters (ecu_global_parameter_id) MATCH SIMPLE
        ON UPDATE NO ACTION ON DELETE NO ACTION
)
    WITH (
        OIDS= FALSE
    );
ALTER TABLE public.ecu_global_parameters_values
    OWNER TO postgres;
GRANT ALL ON TABLE public.ecu_global_parameters_values TO postgres;
GRANT ALL ON TABLE public.ecu_global_parameters_values TO leitwarte;