import { ViewContainerRef, ComponentFactoryResolver } from '@angular/core';
import { SiControl } from 'src/app/si/model/control/si-control';
import { Message } from 'src/app/util/i18n/message';
import { SiComp } from '../../si-comp';
import { SiEntry } from '../../../content/si-entry';
import { SiDeclaration } from '../../../meta/si-declaration';
import { CompactEntryComponent } from '../comp/compact-entry/compact-entry.component';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { CompactEntryModel } from '../comp/compact-entry-model';

export class CompactEntrySiComp implements SiComp, CompactEntryModel {
	public entry: SiEntry|null = null;
	public controls: SiControl[] = [];

	constructor(public declaration: SiDeclaration) {
	}

	getEntries(): SiEntry[] {
		return [this.entry];
	}

	getMessages(): Message[] {
		if (!this.entry) {
			return [];
		}

		return this.entry.getMessages();
	}

	getSelectedEntries(): SiEntry[] {
		return [];
	}

	getSiEntry(): SiEntry|null {
		return this.entry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.declaration;
	}

	createUiStructureModel(): UiStructureModel {
		const uiContent = new TypeUiContent(CompactEntryComponent, (ref, uiStructure) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
		});

		const uiStructureModel =  new SimpleUiStructureModel(uiContent);
		uiStructureModel.controls = this.getControls().map(siControl => siControl.createUiContent());
		uiStructureModel.messagesCallback = () => this.getMessages();
		return uiStructureModel;
	}

	getControls(): SiControl[] {
		const controls: SiControl[] = [];
		controls.push(...this.controls);
		controls.push(...this.entry.selectedEntryBuildup.controls);
		return controls;
	}

	// getFieldDeclarations(): SiFieldDeclaration[] {
	// 	return this.declaration.getFieldDeclarationsByTypeId(this.entry.selectedTypeId);
	// }
}
