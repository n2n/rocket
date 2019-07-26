
import { SiEntry } from 'src/app/si/model/content/si-entry';
import { SiStructureModel } from 'src/app/si/model/structure/si-structure-model';

export interface SiComp extends SiStructureModel {

// 	getZone(): SiZone;

	reload(): void;

	getEntries(): SiEntry[];

	getSelectedEntries(): SiEntry[];
}
