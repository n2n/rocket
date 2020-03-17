
import { SiEmbeddedEntry } from './si-embedded-entry';
import { EmbeddedEntriesConfig } from './embedded-entries-config';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiEntryIdentifier } from '../../../si-qualifier';
import { EmbeddedEntryObtainer } from './embedded-entry-obtainer';
import {
	SiGenericEmbeddedEntryCollection, SiGenericEmbeddedEntry, SiEmbeddedEntryResetPointCollection
} from './generic-embedded';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { EmbeddedEntriesInUiStructureModel } from './embedded-entries-in-ui-structure-model';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';

export class EmbeddedEntriesInSiField extends SiFieldAdapter {

	config: EmbeddedEntriesConfig = {
		min: 0,
		max: null,
		reduced: false,
		nonNewRemovable: true,
		sortable: false,
		allowedSiTypeQualifiers: null
	};

	constructor(private siService: SiService, private typeCategory: string, private apiUrl: string,
			private translationService: TranslationService, private values: SiEmbeddedEntry[] = []) {
		super();
	}

	hasInput(): boolean {
		return true;
	}

	readInput(): object {
		return { entryInputs: this.values.map(embeddedEntry => embeddedEntry.entry.readInput() ) };
	}


	createUiStructureModel(): UiStructureModel {
		return new EmbeddedEntriesInUiStructureModel(
				new EmbeddedEntryObtainer(this.siService, this.apiUrl, this.config.reduced), 
				this.typeCategory, this.values, this.config, this.translationService,
				this.disabledSubject);
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
