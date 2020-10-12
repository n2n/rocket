
import { Message } from 'src/app/util/i18n/message';
import { SiComp } from '../../si-comp';
import { SiEntry } from '../../../content/si-entry';
import { UiStructureModel } from 'src/app/ui/structure/model/ui-structure-model';
import { ListZoneContentComponent } from '../comp/list-zone-content/list-zone-content.component';
import { SiPageCollection } from './si-page-collection';
import { EntriesListModel } from '../comp/entries-list-model';
import { SimpleUiStructureModel } from 'src/app/ui/structure/model/impl/simple-si-structure-model';
import { TypeUiContent } from 'src/app/ui/structure/model/impl/type-si-content';
import { SiEntryQualifierSelection } from './si-entry-qualifier-selection';
import { PaginationComponent } from '../comp/pagination/pagination.component';
import { SiControl } from '../../../control/si-control';

export class EntriesListSiComp implements SiComp, EntriesListModel {

	public qualifierSelection: SiEntryQualifierSelection|null = null;
	readonly pageCollection: SiPageCollection;
	controls: SiControl[] = [];

	constructor(public apiUrl: string, pageSize: number) {
		this.pageCollection = new SiPageCollection(pageSize);
// 		this.qualifierSelection = {
// 			min: 0,
// 			max: 1,
// 			selectedQualfiers: [],
//
// 			done: () => { },
//
// 			cancel: () => { }
// 		}
	}

	getApiUrl(): string {
		return this.apiUrl;
	}

	getSiEntryQualifierSelection(): SiEntryQualifierSelection|null {
		return this.qualifierSelection;
	}

	getSiPageCollection(): SiPageCollection {
		return this.pageCollection;
	}

	getSiComp(): SiComp {
		return this;
	}

	getEntries(): SiEntry[] {
		const entries = [];
		for (const page of this.pageCollection.pages) {
			if (page.entries) {
				entries.push(...page.entries);
			}
		}
		return entries;
	}

	getSelectedEntries(): SiEntry[] {
		throw new Error('Method not implemented.');
	}

	createUiStructureModel(): UiStructureModel {
		const uiStrucuterModel = new SimpleUiStructureModel();

		uiStrucuterModel.initCallback = (uiStructure) => {
			uiStrucuterModel.content = new TypeUiContent(ListZoneContentComponent, (ref) => {
				ref.instance.model = this;
				ref.instance.uiStructure = uiStructure;
			});

			uiStrucuterModel.mainControlContents = this.controls.map((control) => {
				return control.createUiContent(uiStructure.getZone());
			});
		};

		uiStrucuterModel.asideContents = [new TypeUiContent(PaginationComponent, (ref) => {
			ref.instance.siPageCollection = this.pageCollection;
		})];

		uiStrucuterModel.messagesCallback = () => this.getMessages();

		return uiStrucuterModel;
	}

	private getMessages(): Message[] {
		const messages: Message[] = [];

		for (const entry of this.getEntries()) {
			messages.push(...entry.getMessages());
		}

		return messages;
	}
}



