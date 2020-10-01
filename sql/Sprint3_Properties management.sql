ALTER TABLE public.vehicle_variant_data
    ADD COLUMN sw_preset_type character(1);
ALTER TABLE public.vehicle_variant_data_history
    ADD COLUMN sw_preset_type character(1);
