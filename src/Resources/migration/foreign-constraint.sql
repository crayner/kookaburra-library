ALTER TABLE `__prefix__Library`
    ADD CONSTRAINT  FOREIGN KEY (`facility`) REFERENCES `__prefix__Facility` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`department`) REFERENCES `__prefix__Department` (`id`);
ALTER TABLE `__prefix__LibraryItem`
    ADD CONSTRAINT  FOREIGN KEY (`department`) REFERENCES `__prefix__Department` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`status_recorder`) REFERENCES `__prefix__Person` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`library`) REFERENCES `__prefix__Library` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`person_ownership`) REFERENCES `__prefix__Person` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`facility`) REFERENCES `__prefix__Facility` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`created_by`) REFERENCES `__prefix__Person` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`responsible_for_status`) REFERENCES `__prefix__Person` (`id`);
ALTER TABLE `__prefix__LibraryItemEvent`
    ADD CONSTRAINT  FOREIGN KEY (`person_in`) REFERENCES `__prefix__Person` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`library_item`) REFERENCES `__prefix__LibraryItem` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`person_out`) REFERENCES `__prefix__Person` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`responsible_for_status`) REFERENCES `__prefix__Person` (`id`);
ALTER TABLE `__prefix__LibraryReturnAction`
    ADD CONSTRAINT  FOREIGN KEY (`action_by`) REFERENCES `__prefix__Person` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`library_item`) REFERENCES `__prefix__LibraryItem` (`id`),
    ADD CONSTRAINT  FOREIGN KEY (`created_by`) REFERENCES `__prefix__Person` (`id`);
