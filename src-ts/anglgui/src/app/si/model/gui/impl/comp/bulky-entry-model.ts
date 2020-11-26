import { SiEntry } from '../../../content/si-entry';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export interface BulkyEntryModel {

	getSiEntry(): SiEntry;

	getContentUiStructure(): UiStructure;
}
