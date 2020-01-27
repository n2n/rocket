import { SiComp } from '../../si-comp';
import { SiDeclaration } from '../../../meta/si-declaration';
import { SiEntry } from '../../../content/si-entry';
import { SiControl } from '../../../control/si-control';
import { Message } from 'src/app/util/i18n/message';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { BulkyEntryComponent } from '../comp/bulky-entry/bulky-entry.component';
import { BulkyEntryModel } from '../comp/bulky-entry-model';

export class BulkyEntrySiComp implements SiComp, BulkyEntryModel {

	constructor(public declaration: SiDeclaration) {
	}

	private _entry: SiEntry|null = null;
	public controls: Array<SiControl> = [];

	getEntries(): SiEntry[] {
		return [this.entry];
	}

	getMessages(): Message[] {
		return [];
	}

	getSelectedEntries(): SiEntry[] {
		return [];
	}

	reload() {
	}

	getContent() {
		return this;
	}

	get entry(): SiEntry|null {
		return this._entry;
	}

	set entry(entry: SiEntry|null) {
		this._entry = entry;
	}

	getSiEntry(): SiEntry {
		return this._entry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.declaration;
	}

	createUiStructureModel(): UiStructureModel {
		const uiStructureModel = new SimpleUiStructureModel();

		uiStructureModel.initCallback = (uiStructure) => {
			uiStructureModel.content = new TypeUiContent(BulkyEntryComponent, (ref) => {
				ref.instance.model = this;
				ref.instance.uiStructure = uiStructure;
			});

			uiStructureModel.controls = this.getControls()
					.map(control => control.createUiContent(uiStructure.getZone()));
		};

		return uiStructureModel;
	}

	getControls(): SiControl[] {
		const controls: SiControl[] = [];
		controls.push(...this.controls);
		controls.push(...this.entry.selectedEntryBuildup.controls);
		return controls;
	}
}
