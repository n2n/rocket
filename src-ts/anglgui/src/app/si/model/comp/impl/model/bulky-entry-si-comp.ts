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
import { SiUiStructureModelFactory } from './si-ui-structure-model-factory';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiProp } from '../../../meta/si-prop';
import { SiField } from '../../../content/si-field';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { Subscription, Observable } from 'rxjs';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { SiStructureDeclaration } from '../../../meta/si-structure-declaration';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { StructureBranchComponent } from 'src/app/ui/structure/comp/structure-branch/structure-branch.component';
import { EnumInComponent } from '../../../content/impl/enum/comp/enum-in/enum-in.component';
import { EnumInModel } from '../../../content/impl/enum/comp/enum-in-model';
import { createOptional } from '@angular/compiler/src/core';

export class BulkyEntrySiComp implements SiComp {
	private _entry: SiEntry|null = null;
	public controls: Array<SiControl> = [];

	constructor(public declaration: SiDeclaration) {
	}

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

	createUiStructureModel(): UiStructureModel {
		return new BulkyUiStructureModel(this.entry, this.declaration);
	}

	getControls(): SiControl[] {
		const controls: SiControl[] = [];
		controls.push(...this.controls);
		controls.push(...this.entry.selectedEntryBuildup.controls);
		return controls;
	}
}

class BulkyUiStructureModel extends UiStructureModelAdapter implements BulkyEntryModel {

	private contentUiStructures: UiStructure[] = [];
	private subscription: Subscription|null = null;

	constructor(private siEntry: SiEntry, private siDeclaration: SiDeclaration) {
		super();
	}

	getSiEntry(): SiEntry {
		return this.siEntry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.siDeclaration;
	}

	// getContentUiStructures(): UiStructure[] {
	// 	return this.contentUiStructures;
	// }

	init(uiStructure: UiStructure): void {
		if (this.content) {
			throw new IllegalSiStateError('Already initialized.');
		}

		if (!this.siEntry.isMultiType()) {
			this.rebuildStructures(uiStructure);
		} else {
			this.subscription = this.siEntry.selectedTypeId$.subscribe(() => {
				this.rebuildStructures(uiStructure);
			});

			uiStructure.createToolbarChild(this.createTypeSwitchUiStructureModel());
		}

		this.content = new TypeUiContent(BulkyEntryComponent, (ref) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
		});
	}

	private createTypeSwitchUiStructureModel() {
		return new SimpleUiStructureModel(new TypeUiContent(EnumInComponent, (ref) => {
			ref.instance.model = new TypeSelectInModel(this.siEntry);
		}));
	}

	destroy(): void {
		this.clear();

		if (this.subscription) {
			this.subscription.unsubscribe();
		}
	}

	private clear() {
		let contentUiStructure: UiStructure;
		while (contentUiStructure = this.contentUiStructures.pop()) {
			contentUiStructure.dispose();
		}
	}

	private rebuildStructures(uiStructure: UiStructure) {
		this.clear();

		this.asideContents = this.siEntry.selectedEntryBuildup.controls
					.map(control => control.createUiContent(uiStructure.getZone()));

		const siTypeDeclaration = this.siDeclaration.getTypeDeclarationByTypeId(this.siEntry.selectedTypeId);
		const toolbarResolver = new ToolbarResolver();

		this.contentUiStructures = this.createStructures(uiStructure, siTypeDeclaration.structureDeclarations, toolbarResolver);

		for (const prop of siTypeDeclaration.type.getProps()) {
			if (prop.dependantPropIds.length > 0 && this.siEntry.selectedEntryBuildup.containsPropId(prop.id)) {
				toolbarResolver.fillContext(prop, this.siEntry.selectedEntryBuildup.getFieldById(prop.id));
			}
		}
	}

	private createStructures(parent: UiStructure, uiStructureDeclarations: SiStructureDeclaration[],
			toolbarResolver: ToolbarResolver): UiStructure[] {
		const structures: UiStructure[] = [];
		for (const usd of uiStructureDeclarations) {
			structures.push(this.dingsel(parent, usd, toolbarResolver));
		}
		return structures;
	}

	private dingsel(parent: UiStructure, ssd: SiStructureDeclaration, toolbarResolver: ToolbarResolver): UiStructure {
		const uiStructure = parent.createContentChild();
		uiStructure.label = ssd.prop ? ssd.prop.label : ssd.label;
		uiStructure.type = ssd.type;

		if (ssd.prop) {
			uiStructure.label = ssd.prop.label;
			const siField = this.siEntry.selectedEntryBuildup.getFieldById(ssd.prop.id);
			uiStructure.model = siField.createUiStructureModel();
			toolbarResolver.register(ssd.prop.id, uiStructure);
			return uiStructure;
		}

		uiStructure.label = ssd.label;
		uiStructure.model = new SimpleUiStructureModel(
				new TypeUiContent(StructureBranchComponent, (ref) => {
					ref.instance.uiStructure = uiStructure;
				}));

		this.createStructures(uiStructure, ssd.children, toolbarResolver);

		return uiStructure;
	}
}

class TypeSelectInModel implements EnumInModel {
	private options = new Map<string, string>();

	constructor(private siEntry: SiEntry) {
		for (const tq of siEntry.typeQualifiers) {
			this.options.set(tq.id, tq.name);
		}
	}

	getValue(): string {
		return this.siEntry.selectedTypeId;
	}

	setValue(value: string): void {
		this.siEntry.selectedTypeId = value;
	}

	getOptions(): Map<string, string> {
		return this.options;
	}
}

class ToolbarResolver {
	private uiStructuresMap = new Map<string, UiStructure>();

	register(propId: string, uiStructure: UiStructure) {
		this.uiStructuresMap.set(propId, uiStructure);
	}

	fillContext(conextSiProp: SiProp, contextSiField: SiField) {
		let contextUiStructure: UiStructure|null = null;

		for (const dependantPropId of conextSiProp.dependantPropIds) {
			const uiStructure = this.uiStructuresMap.get(dependantPropId);

			if (!uiStructure) {
				continue;
			}

			if (!contextUiStructure) {
				contextUiStructure = uiStructure;
			}

			contextUiStructure = this.deterOuter(contextUiStructure, uiStructure);
		}

		if (contextUiStructure) {
			contextUiStructure.createToolbarChild(contextSiField.createUiStructureModel());
		}
	}

	private deterOuter(uiStructure1: UiStructure, uiStructure2: UiStructure): UiStructure {
		if (uiStructure1 === uiStructure2) {
			return uiStructure1;
		}

		if (uiStructure1.containsDescendant(uiStructure2)) {
			return uiStructure1;
		}

		if (uiStructure2.containsDescendant(uiStructure1)) {
			return uiStructure2;
		}

		return this.deterOuter(uiStructure1.parent, uiStructure2.parent);
	}

}

