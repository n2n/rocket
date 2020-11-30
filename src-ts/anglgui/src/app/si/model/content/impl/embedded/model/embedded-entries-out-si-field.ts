
import { SiEmbeddedEntry } from './si-embedded-entry';
import { SiService } from 'src/app/si/manage/si.service';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { TranslationService } from 'src/app/util/i18n/translation.service';
import { SiFrame } from 'src/app/si/model/meta/si-frame';
import { SiModStateService } from 'src/app/si/model/mod/model/si-mod-state.service';
import { EmbeOutSource } from './embe/embe-collection';
import { EmbeddedEntriesOutConfig } from './embe/embedded-entries-config';
import { EmbeddedEntriesOutUiStructureModel } from './embedded-entries-out-ui-structure-model';
import { GenericEmbeddedEntryManager } from './generic/generic-embedded-entry-manager';
import { SiFieldAdapter } from '../../common/model/si-field-adapter';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';

export class EmbeddedEntriesOutSiField extends SiFieldAdapter implements EmbeOutSource {

	config: EmbeddedEntriesOutConfig = {
	 	reduced: false,
	};

	constructor(private siService: SiService, private siModState: SiModStateService,
			private frame: SiFrame, private translationService: TranslationService, private values: SiEmbeddedEntry[] = []) {
		super();
	}

	setValues(values: SiEmbeddedEntry[]) {
		this.values = values;
	}

	getValues(): SiEmbeddedEntry[] {
		return this.values;
	}

	createUiStructureModel(): UiStructureModel {
		return new EmbeddedEntriesOutUiStructureModel(this.frame, this, this.config, this.translationService,
				this.disabledSubject);
	}

	hasInput(): boolean {
		return false;
	}

	readInput(): object {
		throw new IllegalSiStateError('no input');
	}

	private createGenericManager(): GenericEmbeddedEntryManager {
		return new GenericEmbeddedEntryManager(this.values, this.siService, this.siModState, this.frame, this,
				this.config.reduced, null);
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


