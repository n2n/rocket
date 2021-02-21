
import { SiGui } from '../../si-gui';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { CompactExplorerComponent } from '../comp/compact-explorer/compact-explorer.component';
import { SiPageCollection } from './si-page-collection';
import { CompactExplorerModel } from '../comp/compact-explorer-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiEntryQualifierSelection } from './si-entry-qualifier-selection';
import { PaginationComponent } from '../comp/pagination/pagination.component';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiService } from 'src/app/si/manage/si.service';
import { SiModStateService } from '../../../mod/model/si-mod-state.service';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { StructureUiZoneError } from 'src/app/ui/structure/model/impl/structure-ui-zone-error';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { SiPartialContent } from '../../../content/si-partial-content';
import { SiFrame } from '../../../meta/si-frame';
import { StructurePageManager } from '../comp/compact-explorer/structure-page-manager';
import { Observable, from } from 'rxjs';
import { Message } from 'src/app/util/i18n/message';
import { UiStructureError } from 'src/app/ui/structure/model/ui-structure-error';

export class CompactExplorerSiGui implements SiGui {

	qualifierSelection: SiEntryQualifierSelection|null = null;
	pageCollection: SiPageCollection;
	partialContent: SiPartialContent|null = null;

	constructor(pageSize: number, frame: SiFrame, siService: SiService,
			siModState: SiModStateService) {

		this.pageCollection = new SiPageCollection(pageSize, frame, siService, siModState);
	}

	// getEntries(): SiEntry[] {
	// 	return [];// this.pageCollection.getEntries();
	// }

	// getSelectedEntries(): SiEntry[] {
	// 	throw new Error('Method not implemented.');
	// }

	createUiStructureModel(): UiStructureModel {
		return new CompactExplorerListModelImpl(this, this.partialContent);
	}
}

class CompactExplorerListModelImpl extends UiStructureModelAdapter implements CompactExplorerModel {
	private structurePageManager: StructurePageManager;

	constructor(private comp: CompactExplorerSiGui, partialContent: SiPartialContent|null) {
		super();

		if (partialContent && !this.comp.pageCollection.declared) {
			this.comp.pageCollection.size = partialContent.count;
			if (partialContent.count > 0) {
				this.comp.pageCollection.createPage(1, partialContent.entries);
			}
		}
	}

	getStructurePageManager(): StructurePageManager {
		return this.structurePageManager;
	}

	getSiEntryQualifierSelection(): SiEntryQualifierSelection {
		return this.comp.qualifierSelection;
	}

	bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		this.structurePageManager = new StructurePageManager(uiStructure, this.comp.pageCollection);
		// because of changes after view check;
		this.structurePageManager.loadSingle(1, 0);

		let comp: CompactExplorerComponent;
		let pagiComp: PaginationComponent;

		this.uiContent = new TypeUiContent(CompactExplorerComponent, (ref) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
			comp = ref.instance;

			if (pagiComp) {
				pagiComp.cec = comp;
			}
		});

		this.asideUiContents = [new TypeUiContent(PaginationComponent, (aisdeRef) => {
			pagiComp = aisdeRef.instance;
			if (comp) {
				aisdeRef.instance.cec = comp;
			}
		})];
	}

	unbind() {
		super.unbind();
		this.comp.pageCollection.clear();
	}

	getMainControlContents(): UiContent[] {
		if (!this.comp.pageCollection.controls ||
				this.comp.pageCollection.controls.length === this.mainControlUiContents.length ) {
			return this.mainControlUiContents;
		}

		return this.mainControlUiContents = this.comp.pageCollection.controls.map((control) => {
			return control.createUiContent(() => this.boundUiStructure.getZone());
		});
	}

	getMessages(): Message[] {
		return [];
	}

	getStructureErrors(): UiStructureError[] {
		return [];
	}

	getStructureErrors$(): Observable<UiStructureError[]> {
		return from([]);
	}

	getStructures$(): Observable<UiStructure[]> {
		return this.structurePageManager.getUiStructures$();
	}

	// getZoneErrors(): UiZoneError[] {
	// 	const uiZoneErrors: UiZoneError[] = [];
	// 	for (const entry of this.comp.pageCollection.getEntries()) {
	// 		uiZoneErrors.push(...entry.getMessages()
	// 				.map((message) => new StructureUiZoneError(message, this.reqBoundUiStructure())));
	// 	}
	// 	return uiZoneErrors;
	// }
}

