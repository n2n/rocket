
import { SiGui } from '../../si-gui';
import { SiEntry } from '../../../content/si-entry';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { CompactExplorerComponent } from '../comp/compact-explorer/compact-explorer.component';
import { SiPageCollection } from './si-page-collection';
import { CompactExplorerModel } from '../comp/compact-explorer-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiEntryQualifierSelection } from './si-entry-qualifier-selection';
import { PaginationComponent } from '../comp/pagination/pagination.component';
import { UiStructure } from 'src/app/ui/structure/model/ui-structure';
import { SiControlBoundry } from '../../../control/si-control-bountry';
import { SiService } from 'src/app/si/manage/si.service';
import { SiModStateService } from '../../../mod/model/si-mod-state.service';
import { UiContent } from 'src/app/ui/structure/model/ui-content';
import { UiStructureModelAdapter } from 'src/app/ui/structure/model/impl/ui-structure-model-adapter';
import { StructureUiZoneError } from 'src/app/ui/structure/model/impl/structure-ui-zone-error';
import { UiZoneError } from 'src/app/ui/structure/model/ui-zone-error';
import { IllegalSiStateError } from 'src/app/si/util/illegal-si-state-error';
import { SiPartialContent } from '../../../content/si-partial-content';

export class CompactExplorerSiGui implements SiGui {

	qualifierSelection: SiEntryQualifierSelection|null = null;
	pageCollection: SiPageCollection;
	partialContent: SiPartialContent|null = null;

	constructor(pageSize: number, apiUrl: string, siService: SiService,
			siModState: SiModStateService) {

		this.pageCollection = new SiPageCollection(pageSize, apiUrl, siService, siModState);
	}

	getEntries(): SiEntry[] {
		return this.pageCollection.getEntries();
	}

	getSelectedEntries(): SiEntry[] {
		throw new Error('Method not implemented.');
	}

	createUiStructureModel(): UiStructureModel {
		if (this.pageCollection.pagesNum > 0) {
			throw new IllegalSiStateError('SiPageCollection already in use.');
		}

		return new CompactExplorerListModelImpl(this, this.partialContent);
	}
}

class CompactExplorerListModelImpl extends UiStructureModelAdapter implements CompactExplorerModel {

	constructor(private comp: CompactExplorerSiGui, partialContent: SiPartialContent|null) {
		super();

		if (partialContent) {
			this.comp.pageCollection.size = partialContent.count;
			this.comp.pageCollection.createPage(1, partialContent.entries);
		}
	}

	getSiPageCollection(): SiPageCollection {
		return this.comp.pageCollection;
	}

	getSiEntryQualifierSelection(): SiEntryQualifierSelection {
		return this.comp.qualifierSelection;
	}

	bind(uiStructure: UiStructure): void {
		super.bind(uiStructure);

		this.uiContent = new TypeUiContent(CompactExplorerComponent, (ref) => {
			ref.instance.model = this;
			ref.instance.uiStructure = uiStructure;
		});

		this.asideUiContents = [new TypeUiContent(PaginationComponent, (ref) => {
			ref.instance.siPageCollection = this.getSiPageCollection();
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
			return control.createUiContent(this.boundUiStructure.getZone());
		});
	}

	getZoneErrors(): UiZoneError[] {
		const uiZoneErrors: UiZoneError[] = [];
		for (const entry of this.comp.getEntries()) {
			uiZoneErrors.push(...entry.getMessages()
					.map((message) => new StructureUiZoneError(message, this.reqBoundUiStructure())));
		}
		return uiZoneErrors;
	}
}

