import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SiEntry } from '../content/si-entry';

export interface SiComp {

// 	getZone(): UiZone;

	getEntries(): SiEntry[];

	getSelectedEntries(): SiEntry[];

	createUiStructureModel(): UiStructureModel;
}
