
import { SiEntry } from 'src/app/si/model/entity/si-entry';
import { SiStructureModel } from 'src/app/si/model/structure/ui-structure-model';

export interface SiComp extends SiStructureModel {

// 	getZone(): UiZone;

	reload(): void;

	getEntries(): SiEntry[];

	getSelectedEntries(): SiEntry[];
}
