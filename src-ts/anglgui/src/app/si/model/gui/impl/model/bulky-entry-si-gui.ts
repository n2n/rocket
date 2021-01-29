import { SiGui } from '../../si-gui';
import { SiDeclaration } from '../../../meta/si-declaration';
import { SiEntry, SiEntryState } from '../../../content/si-entry';
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
import { SiStructureDeclaration, UiStructureType, UiStructureTypeUtils } from '../../../meta/si-structure-declaration';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { EnumInComponent } from '../../../content/impl/enum/comp/enum-in/enum-in.component';
import { EnumInModel } from '../../../content/impl/enum/comp/enum-in-model';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { PlainContentComponent } from 'src/app/ui/structure/comp/plain-content/plain-content.component';
import { SiControlBoundry } from '../../../control/si-control-bountry';
import { SiFrame, SiFrameApiSection } from '../../../meta/si-frame';
import { SiEntryMonitor } from '../../../mod/model/si-entry-monitor';
import { SiService } from 'src/app/si/manage/si.service';
import { SiModStateService } from '../../../mod/model/si-mod-state.service';
import { BulkyEntryComponent } from '../comp/bulky-entry/bulky-entry.component';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { StructureBranchComponent } from 'src/app/ui/structure/comp/structure-branch/structure-branch.component';

export class BulkyEntrySiGui implements SiGui, SiControlBoundry {
	private _entry: SiEntry|null = null;
	public entryControlsIncluded = true;
	public controls: Array<SiControl> = [];

	constructor(public siFrame: SiFrame, public declaration: SiDeclaration, public siService: SiService,
			public siModStateService: SiModStateService) {
	}

	getControlledEntries(): SiEntry[] {
		return [this.entry];
	}

	getMessages(): Message[] {
		return [];
	}

	// reload() {
	// }

	// getContent() {
	// 	return this;
	// }

	get entry(): SiEntry|null {
		while (this._entry.replacementEntry) {
			this._entry = this._entry.replacementEntry;
		}
		return this._entry;
	}

	set entry(entry: SiEntry|null) {
		this._entry = entry;
	}

	createUiStructureModel(): UiStructureModel {
		return new BulkyUiStructureModel(this.entry, this.declaration, this.getControls(),
				new SiEntryMonitor(this.siFrame.getApiUrl(SiFrameApiSection.GET), this.siService, 
						this.siModStateService, this.entryControlsIncluded));
	}

	private getControls(): SiControl[] {
		const controls: SiControl[] = [];
		controls.push(...this.controls);
		return controls;
	}
}

class BulkyUiStructureModel extends UiStructureModelAdapter implements BulkyEntryModel {

	private contentUiStructure: UiStructure|null = null;
	private subscription: Subscription|null = null;
	private uiStructureModelCache = new UiStructureModelCache();

	constructor(private siEntry: SiEntry, private siDeclaration: SiDeclaration, private controls: SiControl[],
			private siEntryMonitor: SiEntryMonitor) {
		super();
	}

	getSiEntry(): SiEntry {
		return this.siEntry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.siDeclaration;
	}

	getContentUiStructure(): UiStructure {
		IllegalSiStateError.assertTrue(!!this.contentUiStructure);
		return this.contentUiStructure;
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

		while (this.siEntry.replacementEntry) {
			this.siEntry = this.siEntry.replacementEntry;
		}

		this.subscription = new Subscription();

		if (!this.siEntry.isMultiType()) {
			this.rebuildStructures();
		} else {
			this.subscription.add(this.siEntry.selectedTypeId$.subscribe(() => {
				this.rebuildStructures();
			}));

			uiStructure.createToolbarChild(this.createTypeSwitchUiStructureModel());
		}

		this.siEntryMonitor.start();
		this.monitorEntry();

		this.uiContent = new TypeUiContent(BulkyEntryComponent, (ref) => {
			ref.instance.model = this;
		});

		this.mainControlUiContents = this.controls.map((control) => {
			return control.createUiContent(uiStructure.getZone());
		});
	}

	private monitorEntry() {
		if (!this.siEntry.isNew()) {
			this.siEntryMonitor.registerEntry(this.siEntry);
		}

		const sub = this.siEntry.state$.subscribe((state) => {
			switch (state) {
				case SiEntryState.REPLACED:
					if (!this.siEntry.isNew()) {
						this.siEntryMonitor.unregisterEntry(this.siEntry);
					}
					this.siEntry = this.siEntry.replacementEntry;
					this.subscription.remove(sub);
					this.monitorEntry();
					this.rebuildStructures();
					break;
			}
		});

		this.subscription.add(sub);
	}

	private createTypeSwitchUiStructureModel() {
		return new SimpleUiStructureModel(new TypeUiContent(EnumInComponent, (ref) => {
			ref.instance.model = new TypeSelectInModel(this.siEntry);
		}));
	}

	unbind(): void {
		super.unbind();

		this.siEntryMonitor.stop();
		this.uiContent = null;

		this.clear();

		if (this.subscription) {
			this.subscription.unsubscribe();
			this.subscription = null;
		}
	}

	private clear() {
		if (this.contentUiStructure) {
			this.contentUiStructure.dispose();
		}
		this.contentUiStructure = null;

		this.asideUiContents = [];

		this.uiStructureModelCache.clear();
	}

	private rebuildStructures() {
		this.clear();

		this.asideUiContents = this.siEntry.selectedEntryBuildup.controls
					.map(control => control.createUiContent(this.boundUiStructure.getZone()));

		const siMaskDeclaration = this.siDeclaration.getTypeDeclarationByTypeId(this.siEntry.selectedTypeId);
		const toolbarResolver = new ToolbarResolver();

		this.contentUiStructure = this.boundUiStructure.createChild();
		this.createStructures(this.contentUiStructure, siMaskDeclaration.structureDeclarations, toolbarResolver,
				!this.isBoundStructureInsideGroup());

		for (const prop of siMaskDeclaration.type.getProps()) {
			if (prop.dependantPropIds.length > 0 && this.siEntry.selectedEntryBuildup.containsPropId(prop.id)) {
				toolbarResolver.fillContext(prop, this.siEntry.selectedEntryBuildup.getFieldById(prop.id));
			}
		}
	}

	private isBoundStructureInsideGroup(): boolean {
		let uiStructure = this.boundUiStructure;
		do {
			if (UiStructureTypeUtils.isGroup(uiStructure.type)) {
				return true;
			}
		} while (uiStructure = uiStructure.parent);

		return false;
	}


	private createStructures(parent: UiStructure, uiStructureDeclarations: SiStructureDeclaration[],
			toolbarResolver: ToolbarResolver, groupsRequired: boolean): UiStructure[] {
		const structures: UiStructure[] = [];
		let curUnbUiStructure: UiStructure|null = null;

		for (const usd of uiStructureDeclarations) {
			if (!groupsRequired || UiStructureTypeUtils.isGroup(usd.type)
					|| (usd.type === UiStructureType.PANEL && !this.containsNonGrouped(usd))) {
				structures.push(this.dingsel(parent, usd, toolbarResolver));
				curUnbUiStructure = null;
				continue;
			}

			if (!curUnbUiStructure) {
				curUnbUiStructure = this.createUnbUiStructure(parent);
				structures.push(curUnbUiStructure);
			}

			this.dingsel(curUnbUiStructure, usd, toolbarResolver);
		}

		return structures;
	}

	private containsNonGrouped(siStructureDeclaration: SiStructureDeclaration): boolean {
		if (siStructureDeclaration.children.length === 0) {
			return false;
		}

		for (const child of siStructureDeclaration.children) {
			if (UiStructureTypeUtils.isGroup(child.type)) {
				continue;
			}

			if (child.type === UiStructureType.PANEL && !this.containsNonGrouped(child)) {
				continue;
			}

			return true;
		}

		return false;
	}

	private createUnbUiStructure(parent: UiStructure): UiStructure {
		const curUnbUiStructure = parent.createContentChild(UiStructureType.SIMPLE_GROUP);
		curUnbUiStructure.model = new SimpleUiStructureModel();
		return curUnbUiStructure;
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

		this.createStructures(uiStructure, ssd.children, toolbarResolver, false);

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
			map.set(siFieldId, siField.createUiStructureModel(false));
		}

		return map.get(siFieldId);
	}

	clear(): void {
		this.map.clear();
	}
}

class TypeSelectInModel implements EnumInModel {
	private options = new Map<string, string>();

	constructor(private siEntry: SiEntry) {
		for (const mq of siEntry.maskQualifiers) {
			this.options.set(mq.identifier.entryBuildupId, mq.name);
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

		if (contextUiStructure && contextSiField.isDisplayable()) {
			contextUiStructure.createToolbarChild(contextSiField.createUiStructureModel(false));
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

