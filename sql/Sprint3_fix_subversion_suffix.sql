/*Fix subversion_suffix in revisons table */

UPDATE public.ecu_revisions
SET subversion_suffix = CASE WHEN (subversion_major IS NOT NULL) THEN subversion_suffix ELSE '' END;

