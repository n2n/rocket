import { SiControlFactory } from './si-control-factory';
import { SiDeclaration } from '../model/meta/si-declaration';
import { ObjectMissmatchError, Extractor } from 'src/app/util/mapping/extractor';
import { EntriesListSiComp } from '../model/comp/impl/model/entries-list-si-comp';
import { SiMetaFactory } from './si-meta-factory';
import { SiPage } from '../model/comp/impl/model/si-page';
import { BulkyEntrySiComp } from '../model/comp/impl/model/bulky-entry-si-comp';
import { CompactEntrySiComp } from '../model/comp/impl/model/compact-entry-si-comp';
import { SiComp } from '../model/comp/si-comp';
import { SiEntryIdentifier, SiEntryQualifier } from '../model/content/si-qualifier';
import { SiEntryFactory } from './si-entry-factory';
import { SiEmbeddedEntry } from '../model/content/impl/embedded/model/si-embedded-entry';
import { SiPanel, SiGridPos } from '../model/content/impl/embedded/model/si-panel';
import { SiFile, SiImageDimension } from '../model/content/impl/file/model/file-in-si-field';
import { SiCrumbGroup, SiCrumb } from '../model/content/impl/meta/model/si-crumb';
import { Injector } from '@angular/core';


enum SiCompType {
	ENTRIES_LIST = 'entries-list',
	BULKY_ENTRY = 'bulky-entry',
	COMPACT_ENTRY = 'compact-entry'
}

export class SiCompFactory {

	constructor(private injector: Injector) {
	}

	// createComps(dataArr: Array<any>, requiredType: SiCompType|null = null): SiComp[] {
	// 	const contents = [];
	// 	for (const data of dataArr) {
	// 		contents.push(this.createComp(data, requiredType));
	// 	}
	// 	return contents;
	// }

	static createEntryIdentifier(data: any): SiEntryIdentifier {
		const extr = new Extractor(data);

		return new SiEntryIdentifier(extr.reqString('typeCategory'), extr.nullaString('id'));
	}

	static buildEntryQualifiers(dataArr: any[]|null): SiEntryQualifier[] {
		if (dataArr === null) {
			return null;
		}

		const entryQualifiers: SiEntryQualifier[] = [];
		for (const data of dataArr) {
			entryQualifiers.push(SiCompFactory.createEntryQualifier(data));
		}
		return entryQualifiers;
	}

	static createEntryQualifier(data: any): SiEntryQualifier {
		const extr = new Extractor(data);

		return new SiEntryQualifier(SiMetaFactory.createTypeQualifier(extr.reqObject('typeQualifier')),
				extr.nullaString('id'), extr.nullaString('idName'));
	}

	static createCrumbGroups(dataArr: Array<any>): SiCrumbGroup[] {
		const crumbGroups: SiCrumbGroup[] = [];
		for (const data of dataArr) {
			crumbGroups.push(this.createCrumbGroup(data));
		}
		return crumbGroups;
	}

	static createCrumbGroup(data: any): SiCrumbGroup {
		const extr = new Extractor(data);
		return {
			crumbs: this.createCrumbs(extr.reqArray('crumbs'))
		};
	}

	static createCrumbs(dataArr: Array<any>) {
		const crumbs: SiCrumb[] = [];
		for (const data of dataArr) {
			crumbs.push(this.createCrumb(data));
		}
		return crumbs;
	}

	static createCrumb(data: any): SiCrumb {
		const extr = new Extractor(data);

		switch (extr.reqString('type')) {
			case SiCrumb.Type.LABEL:
				return SiCrumb.createLabel(extr.reqString('label'));
			case SiCrumb.Type.ICON:
				return SiCrumb.createIcon(extr.reqString('iconClass'));
		}
	}

	static buildGridPos(data: any): SiGridPos|null {
		if (data === null) {
			return null;
		}

		const extr = new Extractor(data);

		return {
			colStart: extr.reqNumber('colStart'),
			colEnd: extr.reqNumber('colEnd'),
			rowStart: extr.reqNumber('rowStart'),
			rowEnd: extr.reqNumber('rowEnd')
		};
	}

	static buildSiFile(data: any): SiFile|null {
		if (data === null) {
			return null;
		}

		const extr = new Extractor(data);

		const imageDimensions: SiImageDimension[] = [];
		for (const idData of extr.reqArray('imageDimensions')) {
			imageDimensions.push(SiCompFactory.createSiImageDimension(idData));
		}

		return {
			id: extr.reqObject('id'),
			name: extr.reqString('name'),
			url: extr.nullaString('url'),
			thumbUrl: extr.nullaString('thumbUrl'),
			imageDimensions
		};
	}

	static createSiImageDimension(data: any): SiImageDimension {
		const extr = new Extractor(data);

		return {
			id: extr.reqString('id'),
			name: extr.reqString('name'),
			width: extr.reqNumber('width'),
			height: extr.reqNumber('height')
		};
	}

	createComp(data: any, requiredType: SiCompType|null = null): SiComp {
		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		let compEssentialsFactory: SiControlFactory;
		let declaration: SiDeclaration;

		const type = extr.reqString('type');

		if (!!requiredType && requiredType !== type) {
			throw new ObjectMissmatchError('Type ' + requiredType + ' required. Given: ' + type);
		}

		switch (type) {
			case SiCompType.ENTRIES_LIST:
				const listSiComp = new EntriesListSiComp(dataExtr.reqString('apiUrl'), dataExtr.reqNumber('pageSize'));

				declaration = listSiComp.pageCollection.declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'));

				const partialContentData = dataExtr.nullaObject('partialContent');
				if (partialContentData) {
					const partialContent = new SiEntryFactory(declaration, this.injector)
							.createPartialContent(partialContentData);

					listSiComp.pageCollection.size = partialContent.count;
					listSiComp.pageCollection.putPage(new SiPage(1, partialContent.entries, null));
				}

				return listSiComp;

			case SiCompType.BULKY_ENTRY:
				declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'));
				const bulkyEntryUiContent = new BulkyEntrySiComp(declaration);

				bulkyEntryUiContent.entry = new SiEntryFactory(declaration, this.injector)
						.createEntry(dataExtr.reqObject('entry'));
				return bulkyEntryUiContent;

			case SiCompType.COMPACT_ENTRY:
				declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'));
				const compactEntrySiComp = new CompactEntrySiComp(declaration);

				compEssentialsFactory = new SiControlFactory(compactEntrySiComp, this.injector);
				compactEntrySiComp.controls = compEssentialsFactory.createControls(dataExtr.reqArray('controls'));
				compactEntrySiComp.entry = new SiEntryFactory(declaration, this.injector)
						.createEntry(dataExtr.reqObject('entry'));
				return compactEntrySiComp;

			default:
				throw new ObjectMissmatchError('Invalid si zone type: ' + data.type);
		}
	}

	createEmbeddedEntries(data: Array<any>): SiEmbeddedEntry[] {
		const entries: SiEmbeddedEntry[] = [];
		for (const entryData of data) {
			entries.push(this.createEmbeddedEntry(entryData));
		}
		return entries;
	}

	createEmbeddedEntry(data: any): SiEmbeddedEntry {
		const extr = new Extractor(data);

		return new SiEmbeddedEntry(
				this.createComp(extr.reqObject('content'), SiCompType.BULKY_ENTRY) as BulkyEntrySiComp,
				this.createComp(extr.reqObject('summaryContent'), SiCompType.COMPACT_ENTRY) as CompactEntrySiComp);
	}

	createPanels(data: Array<any>): SiPanel[] {
		const entries: SiPanel[] = [];
		for (const entryData of data) {
			entries.push(this.createPanel(entryData));
		}
		return entries;
	}

	createPanel(data: any): SiPanel {
		const extr = new Extractor(data);

		const panel = new SiPanel(extr.reqString('name'), extr.reqString('label'));
		panel.values = this.createEmbeddedEntries(extr.reqArray('values'));
		panel.reduced = extr.reqBoolean('reduced');
		panel.min = extr.reqNumber('min');
		panel.max = extr.nullaNumber('max');
		panel.nonNewRemovable = extr.reqBoolean('nonNewRemovable');
		panel.sortable = extr.reqBoolean('sortable');
		panel.gridPos = SiCompFactory.buildGridPos(extr.nullaObject('gridPos'));

		const allowedSiTypeQualifiersData = extr.nullaArray('allowedTypeQualifiers');
		if (allowedSiTypeQualifiersData) {
			panel.allowedSiTypeQualifiers = SiMetaFactory.createTypeQualifiers(allowedSiTypeQualifiersData);
		} else {
			panel.allowedSiTypeQualifiers = null;
		}

		return panel;
	}
}
