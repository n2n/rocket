import { SiEntry } from '../../../content/si-entry';
import { SiDeclaration } from '../../../meta/si-declaration';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';

export interface BulkyEntryModel {

	getSiDeclaration(): SiDeclaration;

	getSiEntry(): SiEntry;

	// getContentUiStructures(): UiStructure[];
}
