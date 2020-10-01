ALTER TABLE public.vehicle_variant_data
    ADD COLUMN sw_preset_type CHARACTER(1);
ALTER TABLE public.vehicle_variant_data_history
    ADD COLUMN sw_preset_type CHARACTER(1);
ALTER TABLE public.variant_ecu_revision_mapping
    ADD COLUMN ecu_used BOOLEAN NOT NULL DEFAULT TRUE;