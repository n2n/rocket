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
import { SiPartialContent } from '../../../content/si-partial-content';
import { SiFrame } from '../../../meta/si-frame';
import { StructurePageManager } from '../comp/compact-explorer/structure-page-manager';
import { BehaviorSubject, from, Observable } from 'rxjs';
import { Message } from 'src/app/util/i18n/message';
import { UiStructureError } from 'src/app/ui/structure/model/ui-structure-error';
import { PaginationModel } from '../comp/pagination-model';
import { SiDeclaration } from '../../../meta/si-declaration';
import { SiValueBoundary } from '../../../content/si-value-boundary';

export class CompactExplorerSiGui implements SiGui {

	qualifierSelection: SiEntryQualifierSelection|null = null;
	pageCollection: SiPageCollection;
	partialContent: SiPartialContent|null = null;

	constructor(pageSize: number, frame: SiFrame, maskId: string, siService: SiService,
			siModState: SiModStateService) {

		this.pageCollection = new SiPageCollection(pageSize, frame, maskId, siService, siModState);
	}

	createUiStructureModel(): UiStructureModel {
		return new CompactExplorerListModelImpl(this, this.partialContent);
	}

	getBoundDeclaration(): SiDeclaration {
		return this.pageCollection.declaration ?? new SiDeclaration()
	}

	getBoundValueBoundaries(): SiValueBoundary[] {
		return this.pageCollection.getBoundValueBoundaries();
	}

	getBoundApiUrl(): string | null {
		return this.pageCollection.siFrame.apiUrl;
	}
}

class CompactExplorerListModelImpl extends UiStructureModelAdapter implements CompactExplorerModel, PaginationModel {
	private structurePageManager!: StructurePageManager;
	private currentPageNoSubject?: BehaviorSubject<number>;

	constructor(private comp: CompactExplorerSiGui, partialContent: SiPartialContent|null) {
		super();

		if (partialContent && !this.comp.pageCollection.declared) {
			this.comp.pageCollection.size = partialContent.count;
			if (partialContent.count > 0) {
				this.comp.pageCollection.createPage(1, partialContent.valueBoundaries);
			}
		}
	}

	getStructurePageManager(): StructurePageManager {
		return this.structurePageManager;
	}

	getSiEntryQualifierSelection(): SiEntryQualifierSelection {
		return this.comp.qualifierSelection!;
	}

	getCurrentPageNo$(): Observable<number> {
		return this.currentPageNoSubject!.asObservable();
	}

	set currentPageNo(currentPageNo: number) {
		this.currentPageNoSubject!.next(currentPageNo);
	}

	get currentPageNo(): number {
		return this.currentPageNoSubject!.getValue();
	}

	get pagesNum(): number|null {
		return this.structurePageManager.loadingRequired ? null : this.structurePageManager.possiablePagesNum;
	}

	override bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		this.currentPageNoSubject = new BehaviorSubject<number>(1);

		this.structurePageManager = new StructurePageManager(uiStructure, this.comp.pageCollection);
		// because of changes after view check;
		if (this.structurePageManager.declarationRequired || this.comp.pageCollection.pagesNum > 0) {
			this.structurePageManager.loadSingle(this.currentPageNo, 0);
		}

		this.uiContent = new TypeUiContent(CompactExplorerComponent, (ref) => {
			ref.instance.model = this;
		});

		this.asideUiContents = [new TypeUiContent(PaginationComponent, (asideRef) => {
			asideRef.instance.model = this;
		})];
	}

	override unbind() {
		super.unbind();
		this.comp.pageCollection.clear();
		this.currentPageNoSubject!.unsubscribe();
		this.currentPageNoSubject = undefined;
	}

	override getMainControlContents(): UiContent[] {
		if (!this.comp.pageCollection.controls ||
				this.comp.pageCollection.controls.length === this.mainControlUiContents.length ) {
			return this.mainControlUiContents;
		}

		return this.mainControlUiContents = this.comp.pageCollection.controls.map((control) => {
			return control.createUiContent(() => this.boundUiStructure!.getZone()!);
		});
	}

	getMessages(): Message[] {
		return [];
	}

	getStructureErrors(): UiStructureError[] {
		return [];
	}

	override getStructureErrors$(): Observable<UiStructureError[]> {
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

