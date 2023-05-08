import { SiControl } from 'src/app/si/model/control/si-control';
import { Message } from 'src/app/util/i18n/message';
import { SiGui } from '../../si-gui';
import { SiValueBoundary, SiEntryState } from '../../../content/si-value-boundary';
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
import { SiFrame, SiFrameApiSection } from '../../../meta/si-frame';
import { SiModStateService } from '../../../mod/model/si-mod-state.service';
import { SiService } from 'src/app/si/manage/si.service';
import { IllegalStateError } from 'src/app/util/err/illegal-state-error';

export class CompactEntrySiGui implements SiGui, SiControlBoundry {
	private entrySubject = new BehaviorSubject<SiValueBoundary|null>(null);
	public entryControlsIncluded = true;
	public controls: SiControl[] = [];

	constructor(public siFrame: SiFrame, public declaration: SiDeclaration, public siService: SiService,
			public siModStateService: SiModStateService) {
	}

	get entry(): SiValueBoundary|null {
		return this.entrySubject.getValue();
	}

	set entry(entry: SiValueBoundary|null) {
		this.entrySubject.next(entry);
	}

	// get entry$(): Observable<SiEntry|null> {
	// 	return this.entrySubject.asObservable();
	// }

	getBoundValueBoundaries(): SiValueBoundary[] {
		return [this.entry!];
	}

	getBoundDeclaration(): SiDeclaration {
		return this.declaration;
	}

	getMessages(): Message[] {
		if (!this.entry) {
			return [];
		}

		return this.entry.getMessages();
	}

	getSiEntry(): SiValueBoundary|null {
		return this.entry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.declaration;
	}

	createUiStructureModel(): UiStructureModel {
		return new CompactUiStructureModel(this.entrySubject.asObservable(), this.declaration, this.controls,
				new SiEntryMonitor(this.siFrame.getApiUrl(SiFrameApiSection.GET), this.siService,
						this.siModStateService, this.entryControlsIncluded));
	}

	// getFieldDeclarations(): SiFieldDeclaration[] {
	// 	return this.declaration.getFieldDeclarationsByTypeId(this.entry.selectedTypeId);
	// }
}


class CompactUiStructureModel extends UiStructureModelAdapter implements CompactEntryModel {

	private fieldUiStructuresSubject = new BehaviorSubject<UiStructure[]>([]);
	private subscription: Subscription|null = null;
	private currentSiEntry: SiValueBoundary|null = null;

	constructor(private siEntry$: Observable<SiValueBoundary|null>, private siDeclaration: SiDeclaration, private controls: SiControl[],
				private siEntryMonitor: SiEntryMonitor) {
		super();
	}

	isLoading() {
		return !this.currentSiEntry;
	}

	getSiDeclaration(): SiDeclaration {
		return this.siDeclaration;
	}

	getFieldUiStructures(): UiStructure[] {
		return this.fieldUiStructuresSubject.getValue();
	}

	getStructures$(): Observable<UiStructure[]> {
		return this.fieldUiStructuresSubject.asObservable();
	}

	getMessages(): Message[] {
		return [];
	}



	// getStructureErrors(): UiStructureError[] {
	// 	return [];
	// }

	// getStructureErrors$(): Observable<UiStructureError[]> {
	// 	return from([]);
	// }

	// getZoneErrors(): UiZoneError[] {
	// 	if (!this.currentSiEntry) {
	// 		return [];
	// 	}

	// 	const zoneErrors = new Array<UiZoneError>();
	// 	const typeId = this.currentSiEntry.selectedTypeId;

	// 	if (!typeId) {
	// 		return zoneErrors;
	// 	}

	// 	for (const fieldUiStructure of this.fieldUiStructures) {
	// 		zoneErrors.push(...fieldUiStructure.getZoneErrors());
	// 	}
	// 	return zoneErrors;
	// }

	override bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		this.siEntryMonitor.start();

		this.subscription = new Subscription();

		this.subscription.add(this.siEntry$.subscribe((siValueBoundary) => {
			this.rebuild(siValueBoundary ? siValueBoundary.getFinalReplacementEntry() : null);
		}));

		this.uiContent = new TypeUiContent(CompactEntryComponent, (ref) => {
			ref.instance.model = this;
		});

		this.mainControlUiContents = this.controls.map((control) => {
			return control.createUiContent(() => uiStructure!.getZone()!);
		});
	}


	private rebuild(siValueBoundary: SiValueBoundary|null) {
		this.clear();

		if (!siValueBoundary || !siValueBoundary.entrySelected) {
			return;
		}

		this.currentSiEntry = siValueBoundary;

		this.buildStructures(siValueBoundary);

// 		if (!siValueBoundary.isMultiType()) {
// 			this.rebuild(siValueBoundary);
// 		} else {
// 			this.subscription.add(siValueBoundary.selectedTypeId$.subscribe(() => {
// 				this.rebuild(siValueBoundary);
// 			}));
// 		}

		this.monitorEntry(siValueBoundary);
	}

	private buildStructures(siValueBoundary: SiValueBoundary) {
		const siEntry = siValueBoundary.selectedEntry;
		const siMaskDeclaration = this.siDeclaration.getMaskDeclarationByMaskId(siValueBoundary.selectedMaskId!);

		this.asideUiContents = siEntry.controls
					.map(control => control.createUiContent(() => this.boundUiStructure!.getZone()!));

		const fieldUiStructures = new Array<UiStructure>();
		for (const siProp of siMaskDeclaration.getSiProps()) {
			const structure = new UiStructure(null);
			structure.model = siEntry.getFieldById(siProp.id).createUiStructureModel(true);
			// structure.compact = true;
			fieldUiStructures.push(structure);
		}
		this.fieldUiStructuresSubject.next(fieldUiStructures);
	}

	private monitorEntry(siValueBoundary: SiValueBoundary) {
		if (!siValueBoundary.isNew()) {
			this.siEntryMonitor.registerEntry(siValueBoundary);
		}

		const sub = siValueBoundary.state$.subscribe((state) => {
			switch (state) {
				case SiEntryState.REPLACED:
					if (!siValueBoundary.isNew()) {
						this.siEntryMonitor.unregisterEntry(siValueBoundary);
					}
					this.subscription!.remove(sub);
					this.rebuild(siValueBoundary.replacementValueBoundary);
					break;
			}
		});

		this.subscription!.add(sub);
	}

	override unbind(): void {
		super.unbind();

		this.clear();

		this.siEntryMonitor.stop();
		IllegalStateError.assertTrue(this.siEntryMonitor.size === 0,
				'Remaining monitor entries: ' + this.siEntryMonitor.size);

		this.uiContent = null;


		this.mainControlUiContents = [];

		if (this.subscription) {
			this.subscription.unsubscribe();
			this.subscription = null;
		}
	}

	private clear() {
		if (this.currentSiEntry) {
			if (!this.currentSiEntry.isNew()) {
				this.siEntryMonitor.unregisterEntry(this.currentSiEntry);
			}
			this.currentSiEntry = null;
		}

		for (const fieldUiStructure of this.fieldUiStructuresSubject.getValue()) {
			fieldUiStructure.dispose();
		}
		this.fieldUiStructuresSubject.next([]);

		this.asideUiContents = [];
	}



}
