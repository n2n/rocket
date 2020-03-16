
import { EmbeddedEntriesInUiContent } from './embedded-entries-in-si-content';
import { SiEmbeddedEntry } from './si-embedded-entry';
import { InSiFieldAdapter } from '../../common/model/in-si-field-adapter';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { EmbeddedEntriesConfig } from './embedded-entries-config';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiEntryIdentifier } from '../../../si-qualifier';
import { EmbeddedEntryObtainer } from './embedded-entry-obtainer';
import {
	SiGenericEmbeddedEntryCollection, SiGenericEmbeddedEntry, SiEmbeddedEntryResetPointCollection
} from './generic-embedded';

export class EmbeddedEntryInSiField extends InSiFieldAdapter	{

	config = new EmbeddedEntriesConfig();

	constructor(private siService: SiService, private typeCategory: string, private apiUrl: string, private values: SiEmbeddedEntry[] = []) {
		super();
	}

	readInput(): object {
		return { entryInputs: this.values.map(embeddedEntry => embeddedEntry.entry.readInput() ) };
	}

	createUiContent(uiStructure: UiStructure): UiContent {
		return new EmbeddedEntriesInUiContent(this.siService, this.typeCategory, this.apiUrl, this.values, uiStructure, this.config);
	}

	// copy(): SiField {
	// 	throw new Error('not yet implemented');
	// }

	private findCurrentValue(entryIdentifier: SiEntryIdentifier): SiEmbeddedEntry|null {
		return this.values.find(embeddedEntry => embeddedEntry.entry.identifier.equals(entryIdentifier)) || null;
	}

	copyValue(): SiGenericValue {
		return new SiGenericValue(new SiGenericEmbeddedEntryCollection(
				this.values.map(embeddedEntry => embeddedEntry.copy())));
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		const newEmbeInds = new Array<EmbeInd>();

		const collection = genericValue.readInstance(SiGenericEmbeddedEntryCollection);
		for (const genericEmbedddedEntry of collection.siGenericEmbeddedEntries) {
			const entryIdentifier = genericEmbedddedEntry.genericEntry.identifier;
			this.valEntryIdentifier(entryIdentifier);

			newEmbeInds.push({
				embeddedEntry: this.findCurrentValue(entryIdentifier),
				genericEmbeddedEntry: genericEmbedddedEntry
			});
		}

		const newEntryIdentifiers = newEmbeInds.filter(embeInd => embeInd.embeddedEntry === null).map(() => null);
		if (newEntryIdentifiers.length === 0) {
			return this.handlePaste(newEmbeInds, []);
		}

		const obtainer = new EmbeddedEntryObtainer(this.siService, this.apiUrl, this.config.reduced);
		return obtainer.obtain(newEntryIdentifiers).toPromise()
				.then((embeddedEntries) => {
					return this.handlePaste(newEmbeInds, embeddedEntries);
				});
	}

	private handlePaste(embeInds: Array<EmbeInd>, newEmbeddedEntries: SiEmbeddedEntry[]): Promise<void> {
		const pastePromises: Array<Promise<void>> = [];

		const values = new Array<SiEmbeddedEntry>();
		for (const inf of embeInds) {
			let embeddedEntry = inf.embeddedEntry;
			if (!embeddedEntry) {
				embeddedEntry = newEmbeddedEntries.shift();
			}

			pastePromises.push(embeddedEntry.paste(inf.genericEmbeddedEntry));
			values.push(embeddedEntry);
		}
		this.values = values;

		return Promise.all(pastePromises).then(() => { return; });
	}

	createResetPoint(): SiGenericValue {
		return new SiGenericValue(new SiEmbeddedEntryResetPointCollection(this,
				this.values.map(embeddedEntry => embeddedEntry.createResetPoint())));
	}

	resetToPoint(genericValue: SiGenericValue): void {
		const collection = genericValue.readInstance(SiEmbeddedEntryResetPointCollection);
		if (collection.origSiField !== this) {
			throw new GenericMissmatchError('Reset point belongs to diffrent field.');
		}

		const values = new Array<SiEmbeddedEntry>();
		for (const resetPoint of collection.genercEntryResetPoints) {
			this.valEntryIdentifier(resetPoint.origSiEmbeddedEntry.entry.identifier);

			resetPoint.origSiEmbeddedEntry.resetToPoint(resetPoint.genericEmbeddedEntry);
			values.push(resetPoint.origSiEmbeddedEntry);
		}
		this.values = values;
	}

	private valEntryIdentifier(entryIdentifier: SiEntryIdentifier) {
		if (entryIdentifier.typeCategory !== this.typeCategory) {
			throw new GenericMissmatchError('Categories dont match: '
					+ entryIdentifier.typeCategory + ' != ' + this.typeCategory);
		}
	}

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

interface EmbeInd {
	embeddedEntry: SiEmbeddedEntry|null;
	genericEmbeddedEntry: SiGenericEmbeddedEntry;
}
