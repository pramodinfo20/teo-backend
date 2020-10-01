/*
 * Author: Jakub Kotlorz
 * Purpose: To add functionality of subversions to ecu revisions
 */

ALTER TABLE public.ecu_revisions
    ADD COLUMN subversion_major integer null,
    ADD COLUMN subversion_suffix character varying(50) DEFAULT '',
    ADD UNIQUE (subversion_major, subversion_suffix),
    DROP CONSTRAINT ecu_revisions_unique_sts_version, -- subversions share same sts_version so cannot be unique no more
    ADD UNIQUE (sts_version, subversion_suffix) -- sts_version + subversion_suffix can be unique now
;
