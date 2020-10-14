import { SiComp } from '../../si-comp';
import { SiDeclaration } from '../../../meta/si-declaration';
import { SiEntry } from '../../../content/si-entry';
import { SiControl } from '../../../control/si-control';
import { Message } from 'src/app/util/i18n/message';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { BulkyEntryModel } from '../comp/bulky-entry-model';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiProp } from '../../../meta/si-prop';
import { SiField } from '../../../content/si-field';
import { Subscription } from 'rxjs';
import { SiStructureDeclaration } from '../../../meta/si-structure-declaration';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { EnumInComponent } from '../../../content/impl/enum/comp/enum-in/enum-in.component';
import { EnumInModel } from '../../../content/impl/enum/comp/enum-in-model';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { PlainContentComponent } from 'src/app/ui/structure/comp/plain-content/plain-content.component';

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

	// reload() {
	// }

	// getContent() {
	// 	return this;
	// }

	get entry(): SiEntry|null {
		return this._entry;
	}

	set entry(entry: SiEntry|null) {
		this._entry = entry;
	}

	createUiStructureModel(): UiStructureModel {
		return new BulkyUiStructureModel(this.entry, this.declaration, this.getControls());
	}

	private getControls(): SiControl[] {
		const controls: SiControl[] = [];
		controls.push(...this.controls);
		if (this.entry.entryQualifiers.length === 1) {
			controls.push(...this.entry.selectedEntryBuildup.controls);
		}
		return controls;
	}
}

class BulkyUiStructureModel extends UiStructureModelAdapter implements BulkyEntryModel {

	private contentUiStructures: UiStructure[] = [];
	private subscription: Subscription|null = null;
	private uiStructureModelCache = new UiStructureModelCache();

	constructor(private siEntry: SiEntry, private siDeclaration: SiDeclaration, private controls: SiControl[]) {
		super();
	}

	getSiEntry(): SiEntry {
		return this.siEntry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.siDeclaration;
	}

	getZoneErrors(): UiZoneError[] {
		const zoneErrors = new Array<UiZoneError>();
		const typeId = this.siEntry.selectedTypeId;

		if (!typeId) {
			return zoneErrors;
		}

		for (const [fieldId, field] of this.siEntry.selectedEntryBuildup.getFieldMap()) {
			this.uiStructureModelCache.obtain(typeId, fieldId, field).getZoneErrors();
		}
		return zoneErrors;
	}

	// getContentUiStructures(): UiStructure[] {
	// 	return this.contentUiStructures;
	// }

	bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		if (!this.siEntry.isMultiType()) {
			this.rebuildStructures(uiStructure);
		} else {
			this.subscription = this.siEntry.selectedTypeId$.subscribe(() => {
				this.rebuildStructures(uiStructure);
			});

			uiStructure.createToolbarChild(this.createTypeSwitchUiStructureModel());
		}

		this.mainControlUiContents = this.controls.map((control) => {
			return control.createUiContent(uiStructure.getZone());
		});
	}

	private createTypeSwitchUiStructureModel() {
		return new SimpleUiStructureModel(new TypeUiContent(EnumInComponent, (ref) => {
			ref.instance.model = new TypeSelectInModel(this.siEntry);
		}));
	}

	unbind(): void {
		super.unbind();

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

		this.asideUiContents = this.siEntry.selectedEntryBuildup.controls
					.map(control => control.createUiContent(uiStructure.getZone()));

		const siMaskDeclaration = this.siDeclaration.getTypeDeclarationByTypeId(this.siEntry.selectedTypeId);
		const toolbarResolver = new ToolbarResolver();

		this.contentUiStructures = this.createStructures(uiStructure, siMaskDeclaration.structureDeclarations,
				toolbarResolver);

		for (const prop of siMaskDeclaration.type.getProps()) {
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
			if (this.siEntry.selectedEntryBuildup.containsPropId(ssd.prop.id)) {
				uiStructure.model = this.createUiStructureModel(ssd.prop);
			}
			toolbarResolver.register(ssd.prop.id, uiStructure);
			return uiStructure;
		}

		uiStructure.label = ssd.label;
		uiStructure.model = new SimpleUiStructureModel();

		this.createStructures(uiStructure, ssd.children, toolbarResolver);

		return uiStructure;
	}

	private createUiStructureModel(siProp: SiProp): UiStructureModel {
		if (this.siEntry.selectedEntryBuildup.containsPropId(siProp.id)) {
			const siField = this.siEntry.selectedEntryBuildup.getFieldById(siProp.id);
			return this.uiStructureModelCache.obtain(this.siEntry.selectedTypeId, siProp.id, siField);
		}

		return new SimpleUiStructureModel(new TypeUiContent(PlainContentComponent, () => {}));
	}
}

class UiStructureModelCache {
	private map = new Map<string, Map<string, UiStructureModel>>();

	obtain(siTypeId: string, siFieldId: string, siField: SiField): UiStructureModel {
		if (!this.map.has(siTypeId)) {
			this.map.set(siTypeId, new Map());
		}

		const map = this.map.get(siTypeId);
		if (!map.has(siFieldId)) {
			map.set(siFieldId, siField.createUiStructureModel());
		}

		return map.get(siFieldId);
	}
}

class TypeSelectInModel implements EnumInModel {
	private options = new Map<string, string>();

	constructor(private siEntry: SiEntry) {
		for (const mq of siEntry.maskQualifiers) {
			this.options.set(mq.identifier.typeId, mq.name);
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

