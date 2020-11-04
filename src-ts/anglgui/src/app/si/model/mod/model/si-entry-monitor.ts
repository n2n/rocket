import { SiModStateService } from './si-mod-state.service';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiEntry } from '../../content/si-entry';

export class SiEntryMonitor {

	constructor(private modStateService: SiModStateService, private uiStructure: UiStructure) {
	}

	registerEntry(entry: SiEntry) {
		this.modStateService.registerSiEntry(entry);
	}

	unregisterEntry(entry: SiEntry) {
		this.modStateService.unregisterSiEntry(entry);
	}

	get display(): boolean {
		return this.modStateService.containsModEntryIdentifier(entry);
	}
}
