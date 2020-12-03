import { SiControl } from 'src/app/si/model/control/si-control';
import { Message } from 'src/app/util/i18n/message';
import { SiGui } from '../../si-gui';
import { SiEntry, SiEntryState } from '../../../content/si-entry';
import { SiDeclaration } from '../../../meta/si-declaration';
import { CompactEntryComponent } from '../comp/compact-entry/compact-entry.component';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { CompactEntryModel } from '../comp/compact-entry-model';
import { SiControlBoundry } from '../../../control/si-control-bountry';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { Subscription, BehaviorSubject, Observable } from 'rxjs';
import { SiEntryMonitor } from '../../../mod/model/si-entry-monitor';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { SiFrame } from '../../../meta/si-frame';
import { SiModStateService } from '../../../mod/model/si-mod-state.service';
import { SiService } from 'src/app/si/manage/si.service';

export class CompactEntrySiGui implements SiGui, CompactEntryModel, SiControlBoundry {
	private entrySubject = new BehaviorSubject<SiEntry|null>(null);
	public entryControlsIncluded = true;
	public controls: SiControl[] = [];

	constructor(public siFrame: SiFrame, public declaration: SiDeclaration, public siService: SiService,
			public siModStateService: SiModStateService) {
	}

	get entry(): SiEntry|null {
		return this.entrySubject.getValue();
	}

	set entry(entry: SiEntry|null) {
		this.entrySubject.next(entry);
	}

	// get entry$(): Observable<SiEntry|null> {
	// 	return this.entrySubject.asObservable();
	// }

	getControlledEntries(): SiEntry[] {
		return [this.entry];
	}

	getMessages(): Message[] {
		if (!this.entry) {
			return [];
		}

		return this.entry.getMessages();
	}

	getSiEntry(): SiEntry|null {
		return this.entry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.declaration;
	}

	createUiStructureModel(): UiStructureModel {
		return new CompactUiStructureModel(this.entrySubject.asObservable(), this.declaration, this.controls,
				new SiEntryMonitor(this.siFrame.apiUrl, this.siService, this.siModStateService, this.entryControlsIncluded));
	}

	// getFieldDeclarations(): SiFieldDeclaration[] {
	// 	return this.declaration.getFieldDeclarationsByTypeId(this.entry.selectedTypeId);
	// }
}


class CompactUiStructureModel extends UiStructureModelAdapter implements CompactEntryModel {

	private fieldUiStructures: UiStructure[] = []
	private subscription: Subscription|null = null;

	constructor(private siEntry$: Observable<SiEntry>, private siDeclaration: SiDeclaration, private controls: SiControl[],
			private siEntryMonitor: SiEntryMonitor) {
		super();
	}

	getSiEntry(): SiEntry {
		return this.siEntry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.siDeclaration;
	}

	getFieldUiStructures(): UiStructure[] {
		return this.fieldUiStructures;
	}

	getZoneErrors(): UiZoneError[] {
		const zoneErrors = new Array<UiZoneError>();
		const typeId = this.siEntry.selectedTypeId;

		if (!typeId) {
			return zoneErrors;
		}

		for (const fieldUiStructure of this.fieldUiStructures) {
			zoneErrors.push(...fieldUiStructure.getZoneErrors());
		}
		return zoneErrors;
	}

	// getContentUiStructures(): UiStructure[] {
	// 	return this.contentUiStructures;
	// }

	bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		this.subscription = this.siEntry$.subscribe((siEntry) => {
			this.clear();
			if (siEntry) {
				this.rebuildStructures(siEntry);
			}
		});
	}

	private rebuildStructures(siEntry: SiEntry) {}

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
		}

		this.siEntryMonitor.start();
		this.monitorEntry();

		this.uiContent = new TypeUiContent(CompactEntryComponent, (ref) => {
			ref.instance.model = this;
		});

		this.mainControlUiContents = this.controls.map((control) => {
			return control.createUiContent(uiStructure.getZone());
		});
	}

	private monitorEntry() {
		this.siEntryMonitor.registerEntry(this.siEntry);

		const sub = this.siEntry.state$.subscribe((state) => {
			switch (state) {
				case SiEntryState.REPLACED:
					this.siEntryMonitor.unregisterEntry(this.siEntry);
					this.siEntry = this.siEntry.replacementEntry;
					this.subscription.remove(sub);
					this.monitorEntry();
					this.rebuildStructures();
					break;
			}
		});

		this.subscription.add(sub);
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
		let fieldUiStructure: UiStructure;
		while (fieldUiStructure = this.fieldUiStructures.pop()) {
			fieldUiStructure.dispose();
		}

		this.asideUiContents = [];
	}

	private rebuildStructures() {
		this.clear();

		const siEntryBuildup = this.siEntry.selectedEntryBuildup;
		const siMaskDeclaration = this.siDeclaration.getTypeDeclarationByTypeId(this.siEntry.selectedTypeId);

		this.asideUiContents = siEntryBuildup.controls
					.map(control => control.createUiContent(this.boundUiStructure.getZone()));

		for (const siProp of siMaskDeclaration.getSiProps()) {
			const structure = this.boundUiStructure.createChild();
			structure.model = siEntryBuildup.getFieldById(siProp.id).createUiStructureModel();
			this.fieldUiStructures.push(structure);
		}
	}

}
