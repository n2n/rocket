import { Extractor } from '@angular/compiler';
import { SiCompEssentialsFactory } from './si-comp-essentials-factory';
import { SiDeclaration } from '../model/meta/si-declaration';
import { ObjectMissmatchError } from 'src/app/util/mapping/extractor';
import { EntriesListSiComp } from '../model/comp/impl/model/entries-list-si-content';
import { SiMetaFactory } from './si-meta-factory';
import { SiPage } from '../model/comp/impl/model/si-page';
import { BulkyEntrySiComp } from '../model/comp/impl/model/bulky-entry-si-comp';
import { CompactEntrySiComp } from '../model/comp/impl/model/compact-entry-si-comp';
import { SiComp } from '../model/comp/si-comp';
import { SiEntry } from '../model/content/si-entry';
import { SiPartialContent } from '../model/content/si-partial-content';
import { SiEntryBuildup } from '../model/content/si-entry-buildup';


enum SiCompType {
	ENTRIES_LIST = 'entries-list',
	BULKY_ENTRY = 'bulky-entry',
	COMPACT_ENTRY = 'compact-entry'
}

export class SiContentFactory {


	// createComps(dataArr: Array<any>, requiredType: SiCompType|null = null): SiComp[] {
	// 	const contents = [];
	// 	for (const data of dataArr) {
	// 		contents.push(this.createComp(data, requiredType));
	// 	}
	// 	return contents;
	// }

	static createComp(data: any, requiredType: SiCompType|null = null): SiComp {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		let compFactory: SiCompEssentialsFactory;
		let declaration: SiDeclaration;

		const type = extr.reqString('type');

		if (!!requiredType && requiredType !== type) {
			throw new ObjectMissmatchError('Type ' + requiredType + ' required. Given: ' + type);
		}

		switch (type) {
			case SiCompType.ENTRIES_LIST:
				const listSiComp = new EntriesListSiComp(dataExtr.reqString('apiUrl'),
						dataExtr.reqNumber('pageSize'));

				compFactory = new SiCompEssentialsFactory(listSiComp);
				listSiComp.entryDeclaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('entryDeclaration'));

				const partialContentData = dataExtr.nullaObject('partialContent');
				if (partialContentData) {
					const partialContent = compFactory.createPartialContent(partialContentData);

					listSiComp.size = partialContent.count;
					listSiComp.putPage(new SiPage(1, partialContent.entries, null));
				}

				return listSiComp;

			case SiCompType.BULKY_ENTRY:
				declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('entryDeclaration'));
				const bulkyEntrySiContent = new BulkyEntrySiComp(declaration);

				compFactory = new SiCompEssentialsFactory(bulkyEntrySiContent);
				bulkyEntrySiContent.controls = Array.from(compFactory.createControlMap(dataExtr.reqMap('controls')).values());
				bulkyEntrySiContent.entry = compFactory.createEntry(dataExtr.reqObject('entry'));
				return bulkyEntrySiContent;

			case SiCompType.COMPACT_ENTRY:
				declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('entryDeclaration'));
				const compactEntrySiComp = new CompactEntrySiComp(declaration);

				compFactory = new SiCompEssentialsFactory(compactEntrySiComp);
				compactEntrySiComp.controlMap = compFactory.createControlMap(dataExtr.reqMap('controls'));
				compactEntrySiComp.entry = compFactory.createEntry(dataExtr.reqObject('entry'));
				return compactEntrySiComp;

			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}

}

