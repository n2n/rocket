import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { SiGenericEmbeddedEntryCollection, SiGenericEmbeddedEntry, SiEmbeddedEntryResetPointCollection } from './generic-embedded';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { SiService } from 'src/app/si/manage/si.service';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { SiEmbeddedEntry } from '../si-embedded-entry';
import { SiEntryIdentifier } from '../../../../si-entry-qualifier';
import { SiField } from '../../../../si-field';
import { EmbeddedEntryObtainer } from '../embedded-entry-obtainer';

export class GenericEmbeddedEntryManager {

	constructor(private values: SiEmbeddedEntry[], private siService: SiService, private siModState: SiModStateService,
			private siFrame: SiFrame, private origSiField: SiField, private reduced: boolean, private allowedTypeIds: string[]|null) {
	}

	private findCurrentValue(entryIdentifier: SiEntryIdentifier): SiEmbeddedEntry|null {
		return this.values.find(embeddedEntry => embeddedEntry.entry.identifier.equals(entryIdentifier)) || null;
	}

	copyValue(): Promise<SiGenericValue> {
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

		const obtainer = new EmbeddedEntryObtainer(this.siService, this.siModState, this.siFrame, this.reduced,
				this.allowedTypeIds);
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
		return new SiGenericValue(new SiEmbeddedEntryResetPointCollection(this.origSiField,
				this.values.map(embeddedEntry => embeddedEntry.createResetPoint())));
	}

	resetToPoint(genericValue: SiGenericValue): void {
		const collection = genericValue.readInstance(SiEmbeddedEntryResetPointCollection);
		if (collection.origSiField !== this.origSiField) {
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
		if (!this.siFrame.typeContext.containsTypeId(entryIdentifier.typeId)) {
			throw new GenericMissmatchError('Types dont match: '
					+ entryIdentifier.typeId + ' != ' + this.siFrame);
		}
	}
}

interface EmbeInd {
	embeddedEntry: SiEmbeddedEntry|null;
	genericEmbeddedEntry: SiGenericEmbeddedEntry;
}
