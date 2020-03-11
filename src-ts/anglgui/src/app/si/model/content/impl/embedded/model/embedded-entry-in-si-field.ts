
import { EmbeddedEntriesInUiContent } from './embedded-entries-in-si-content';
import { SiEmbeddedEntry } from './si-embedded-entry';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiField } from '../../../si-field';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntriesConfig } from './embedded-entries-config';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { Fresult } from 'src/app/util/err/fresult';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';

export class EmbeddedEntryInSiField extends InSiFieldAdapter	{

	config = new EmbeddedEntriesConfig();

	constructor(private siService: SiService, private apiUrl: string, private values: SiEmbeddedEntry[] = []) {
		super();
	}

	readInput(): object {
		return { entryInputs: this.values.map(embeddedEntry => embeddedEntry.entry.readInput() ) };
	}

	createUiContent(uiStructure: UiStructure): UiContent {
		return new EmbeddedEntriesInUiContent(this.siService, this.apiUrl, this.values, uiStructure, this.config);
	}

	// copy(): SiField {
	// 	throw new Error('not yet implemented');
	// }

	readGenericValue(): SiGenericValue {
		return new SiGenericValue(this.values.map(embeddedEntry => embeddedEntry.readGeneric()));
	}

	writeGenericValue(genericValue: SiGenericValue): Fresult<GenericMissmatchError> {
		throw new Error('Not yet implemented.');
	}

	// writeGenericValue(genericValue: SiGenericValue): Promise<void> {

	// 	const collection = genericValue.readInstance(SiGenericEmbeddedEntryCollection);

	// 	const promises = new Array<Promise<SiEmbeddedEntry>>();

	// 	if (collection.origSiField === this) {
	// 		for (const siGenericEmbedddedEntry of collection.siGenericEmbeddedEntries) {
	// 			promises.push(siGenericEmbedddedEntry.origSiEntry)
	// 		}
	// 	}
	// }

	// asfd(siEntryIdentifiers: SiEntryIdentifier[]) {
	// 	const getInstructions = new Array<SiGetInstruction>();

	// 	const obtainer = new EmbeddedAddPasteObtainer(this.siService, this.apiUrl, this.config.reduced);

	// 	for (const siEntryIdentifier of siEntryIdentifiers) {
	// 		if (siEntryIdentifier.id === null) {
	// 			getInstructions.push(SiGetInstruction.newEntry(siEntryIdentifier.id))
	// 		} else {
	// 			obtainer.
	// 		}
	// 	}

	// 	this.siService.apiGet(this.apiUrl, new SiGetRequest(SiGetInstruction.))
	// }
}
