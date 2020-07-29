
import { SiEmbeddedEntry } from './si-embedded-entry';
import { EmbeddedEntriesConfig } from './embedded-entries-config';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';
import { SiEntryIdentifier } from '../../../si-entry-qualifier';
import { EmbeddedEntryObtainer } from './embedded-entry-obtainer';
import {
	SiGenericEmbeddedEntryCollection, SiGenericEmbeddedEntry, SiEmbeddedEntryResetPointCollection
} from './generic-embedded';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { EmbeddedEntriesInUiStructureModel } from './embedded-entries-in-ui-structure-model';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { Message } from 'src/app/util/i18n/message';
import { EmbeInSource } from './embe-collection';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { GenericEmbeddedEntryManager } from './generic-embedded-entry-manager';

export class EmbeddedEntriesInSiField extends SiFieldAdapter implements EmbeInSource {

	config: EmbeddedEntriesConfig = {
		min: 0,
		max: null,
		reduced: false,
		nonNewRemovable: true,
		sortable: false,
		allowedTypeIds: null
	};

	constructor(private label: string, private siService: SiService, private frame: SiFrame,
			private translationService: TranslationService, private values: SiEmbeddedEntry[] = []) {
		super();
	}

	setValues(values: SiEmbeddedEntry[]) {
		this.values = values;
		this.validate();
	}

	getValues(): SiEmbeddedEntry[] {
		return this.values;
	}

	private validate() {
		this.messages = [];

		if (this.values.length < this.config.min) {
			this.messages.push(Message.createCode('min_elements_err',
					new Map([['{field}', this.label], ['{min}', this.config.min.toString()]])));
		}

		if (this.config.max !== null && this.values.length > this.config.max) {
			this.messages.push(Message.createCode('max_elements_err',
					new Map([['{field}', this.label], ['{max}', this.config.max.toString()]])));
		}
	}

	hasInput(): boolean {
		return true;
	}

	readInput(): object {
		return { entryInputs: this.values.map(embeddedEntry => embeddedEntry.entry.readInput() ) };
	}

	createUiStructureModel(): UiStructureModel {
		return new EmbeddedEntriesInUiStructureModel(
				new EmbeddedEntryObtainer(this.siService, this.frame.apiUrl, this.config.reduced, this.config.allowedTypeIds),
				this.frame, this, this.config, this.translationService,
				this.disabledSubject);
	}

	// copy(): SiField {
	// 	throw new Error('not yet implemented');
	// }

	private createGenericManager(): GenericEmbeddedEntryManager {
		return new GenericEmbeddedEntryManager(this.values, this.siService, this.frame, this, this.config.reduced,
				this.config.allowedTypeIds);
	}

	copyValue(): SiGenericValue {
		return this.createGenericManager().copyValue();
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		return this.createGenericManager().pasteValue(genericValue);
	}

	createResetPoint(): SiGenericValue {
		return this.createGenericManager().createResetPoint();
	}

	resetToPoint(genericValue: SiGenericValue): void {
		return this.createGenericManager().resetToPoint(genericValue);
	}
}


