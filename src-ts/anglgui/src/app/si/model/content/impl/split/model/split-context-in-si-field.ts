import { SiField } from '../../../si-field';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiEntry } from '../../../si-entry';
import { SplitContextSiField, SplitStyle } from './split-context-si-field';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SplitManagerComponent } from '../comp/split-manager/split-manager.component';
import { SplitManagerModel } from '../comp/split-manager-model';
import { SiGenericValue } from 'src/app/si/model/generic/si-generic-value';
import { Fresult } from 'src/app/util/err/fresult';
import { GenericMissmatchError } from 'src/app/si/model/generic/generic-missmatch-error';

export class SplitContextInSiField extends SplitContextSiField implements SplitManagerModel {

	managerStyle: SplitStyle = { iconClass: null, tooltip: null };
	activeKeys = new Array<string>();
	mandatoryKeys = new Array<string>();
	min: number;

	hasInput(): boolean {
		return true;
	}

	readInput(): object {
		const entryInputObj = {};
		for (const [, splitContent] of this.splitContentMap) {
			let entry: SiEntry;
			if (entry = splitContent.getLoadedSiEntry()) {
				entryInputObj[splitContent.key] = entry.readInput();
			}
		}
		return {
			entryInputs: entryInputObj
		};
	}

	copy(): SiField {
		throw new Error('Method not implemented.');
	}

	protected createUiContent(): UiContent {
		return new TypeUiContent(SplitManagerComponent, (ref) => {
			ref.instance.model = this;
		});
	}

	isKeyMandatory(key: string): boolean {
		return -1 < this.mandatoryKeys.indexOf(key)
				|| (this.activeKeys.length <= this.min && this.isKeyActive(key));
	}

	isKeyActive(key: string): boolean {
		if (-1 < this.activeKeys.indexOf(key)) {
			return true;
		}

		if (this.activeKeys.length < this.min && this.splitContentMap.has(key)) {
			this.activeKeys.push(key);
			return true;
		}

		return false;
	}

	activateKey(key: string) {
		if (!this.splitContentMap.has(key)) {
			throw new Error('Unknown key: ' + key);
		}

		if (!this.isKeyActive(key)) {
			this.activeKeys.push(key);
		}
	}

	deactivateKey(key: string) {
		const i = this.activeKeys.indexOf(key);

		if (i > -1) {
			this.activeKeys.splice(i, 1);
		}
	}

	getIconClass(): string {
		return this.managerStyle.iconClass || 'fa fa-columns';
	}

	getTooltip(): string|null {
		return this.managerStyle.tooltip;
	}

	copyValue(): SiGenericValue {
		throw new Error('Not yet implemented');
	}

	pasteValue(genericValue: SiGenericValue): Promise<void> {
		throw new Error('Not yet implemented');
	}
}
