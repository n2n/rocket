
import { SiEmbeddedEntry } from './si-embedded-entry';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { EmbeddedEntryObtainer } from './embedded-entry-obtainer';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { EmbeddedEntriesInUiStructureModel } from './embedded-entries-in-ui-structure-model';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { Message } from 'src/app/util/i18n/message';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { EmbeddedEntriesInConfig } from './embe/embedded-entries-config';
import { EmbeInSource, EmbeInCollection } from './embe/embe-collection';
import { GenericEmbeddedEntryManager } from './generic/generic-embedded-entry-manager';
import { SiFieldError } from 'src/app/si/model/input/si-field-error';

export class EmbeddedEntriesInSiField extends SiFieldAdapter implements EmbeInSource {

	config: EmbeddedEntriesInConfig = {
		min: 0,
		max: null,
		reduced: false,
		nonNewRemovable: true,
		sortable: false,
		allowedTypeIds: null
	};

	constructor(private label: string, private siService: SiService, private siModState: SiModStateService,
			private frame: SiFrame, private translationService: TranslationService, private values: SiEmbeddedEntry[] = []) {
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
		this.messagesCollection.clear();

		const values = this.getTypeSelectedValues();

		if (values.length < this.config.min) {
			this.messagesCollection.push(Message.createCode('min_elements_err',
					new Map([['{field}', this.label], ['{min}', this.config.min.toString()]])));
		}

		if (this.config.max !== null && values.length > this.config.max) {
			this.messagesCollection.push(Message.createCode('max_elements_err',
					new Map([['{field}', this.label], ['{max}', this.config.max.toString()]])));
		}
	}

	hasInput(): boolean {
		return true;
	}

	private getTypeSelectedValues(): SiEmbeddedEntry[] {
		return this.values.filter(ee => ee.entry.selectedTypeId);
	}

	readInput(): object {
		return { entryInputs: this.getTypeSelectedValues().map(embeddedEntry => embeddedEntry.entry.readInput() ) };
	}

	handleError(error: SiFieldError): void {
		this.messagesCollection.set(error.messages);

		this.getTypeSelectedValues().forEach((value, index) => {
			if (error.subEntryErrors.has(index.toString())) {
				value.entry.handleError(error.subEntryErrors.get(index.toString()));
			}
		});
	}

	createUiStructureModel(): UiStructureModel {
		const embeInCol = new EmbeInCollection(this, this.config);
		embeInCol.readEmbes();

		return new EmbeddedEntriesInUiStructureModel(
				new EmbeddedEntryObtainer(this.siService, this.siModState, this.frame, this.config.reduced, this.config.allowedTypeIds),
				this.frame, embeInCol, this.config, this.translationService,
				this.getDisabled$());
	}

	// copy(): SiField {
	// 	throw new Error('not yet implemented');
	// }

	private createGenericManager(): GenericEmbeddedEntryManager {
		return new GenericEmbeddedEntryManager(this.values, this.siService, this.siModState, this.frame, this,
				this.config.reduced, this.config.allowedTypeIds);
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


