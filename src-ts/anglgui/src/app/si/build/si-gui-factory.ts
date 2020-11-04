import { SiControlFactory } from './si-control-factory';
import { SiDeclaration } from '../model/meta/si-declaration';
import { ObjectMissmatchError, Extractor } from 'src/app/util/mapping/extractor';
import { CompactExplorerSiGui } from '../model/gui/impl/model/compact-explorer-si-gui';
import { SiMetaFactory } from './si-meta-factory';
import { SiPage } from '../model/gui/impl/model/si-page';
import { BulkyEntrySiGui } from '../model/gui/impl/model/bulky-entry-si-gui';
import { CompactEntrySiGui } from '../model/gui/impl/model/compact-entry-si-gui';
import { SiGui } from '../model/gui/si-gui';
import { SiEntryIdentifier, SiEntryQualifier } from '../model/content/si-entry-qualifier';
import { SiEntryFactory } from './si-entry-factory';
import { SiEmbeddedEntry } from '../model/content/impl/embedded/model/si-embedded-entry';
import { SiPanel, SiGridPos } from '../model/content/impl/embedded/model/si-panel';
import { SiFile, SiImageDimension, SiImageCut } from '../model/content/impl/file/model/file-in-si-field';
import { SiCrumbGroup, SiCrumb } from '../model/content/impl/meta/model/si-crumb';
import { Injector } from '@angular/core';


enum SiGuiType {
	COMPACT_EXPLORER = 'compact-explorer',
	BULKY_ENTRY = 'bulky-entry',
	COMPACT_ENTRY = 'compact-entry'
}

export class SiGuiFactory {

	constructor(private injector: Injector) {
	}

	// createComps(dataArr: Array<any>, requiredType: SiGuiType|null = null): SiGui[] {
	// 	const contents = [];
	// 	for (const data of dataArr) {
	// 		contents.push(this.createComp(data, requiredType));
	// 	}
	// 	return contents;
	// }

	static createEntryIdentifier(data: any): SiEntryIdentifier {
		const extr = new Extractor(data);

		return new SiEntryIdentifier(extr.reqString('typeId'), extr.nullaString('id'));
	}

	static buildEntryQualifiers(dataArr: any[]|null): SiEntryQualifier[] {
		if (dataArr === null) {
			return null;
		}

		const entryQualifiers: SiEntryQualifier[] = [];
		for (const data of dataArr) {
			entryQualifiers.push(SiGuiFactory.createEntryQualifier(data));
		}
		return entryQualifiers;
	}

	static createEntryQualifier(data: any): SiEntryQualifier {
		const extr = new Extractor(data);

		return new SiEntryQualifier(SiMetaFactory.createTypeQualifier(extr.reqObject('maskQualifier')),
				SiGuiFactory.createEntryIdentifier(extr.reqObject('identifier')), extr.nullaString('idName'));
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
			imageDimensions.push(SiGuiFactory.createSiImageDimension(idData));
		}

		const siFile = new SiFile(extr.reqObject('id'), extr.reqString('name'), extr.nullaString('url'));
		siFile.thumbUrl = extr.nullaString('thumbUrl');
		siFile.mimeType = extr.nullaString('mimeType');
		siFile.imageDimensions = imageDimensions;
		return siFile;
	}

	static createSiImageDimension(data: any): SiImageDimension {
		const extr = new Extractor(data);

		return {
			id: extr.reqString('id'),
			name: extr.nullaString('name'),
			width: extr.reqNumber('width'),
			height: extr.reqNumber('height'),
			imageCut: this.createSiImageCut(extr.reqObject('imageCut')),
			ratioFixed: extr.reqBoolean('ratioFixed')
		};
	}

	static createSiImageCut(data: any): SiImageCut {
		const extr = new Extractor(data);

		return new SiImageCut(extr.reqNumber('x'), extr.reqNumber('y'), extr.reqNumber('width'),
				extr.reqNumber('height'), extr.reqBoolean('exists'));
	}

	buildGui(data: any, requiredType: SiGuiType|null = null): SiGui|null {
		if (!data) {
			return null;
		}

		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		let compEssentialsFactory: SiControlFactory;
		let declaration: SiDeclaration;

		const type = extr.reqString('type');

		if (!!requiredType && requiredType !== type) {
			throw new ObjectMissmatchError('Type ' + requiredType + ' required. Given: ' + type);
		}

		switch (type) {
			case SiGuiType.COMPACT_EXPLORER:
				const listSiGui = new CompactExplorerSiGui(dataExtr.reqString('apiUrl'), dataExtr.reqNumber('pageSize'));

				compEssentialsFactory = new SiControlFactory(listSiGui, this.injector);
				listSiGui.controls = compEssentialsFactory.createControls(dataExtr.reqArray('controls'));
				declaration = listSiGui.pageCollection.declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'));

				const partialContentData = dataExtr.nullaObject('partialContent');
				if (partialContentData) {
					const partialContent = new SiEntryFactory(declaration, this.injector)
							.createPartialContent(partialContentData);

					listSiGui.pageCollection.size = partialContent.count;
					listSiGui.pageCollection.putPage(new SiPage(1, partialContent.entries, null));
				}

				return listSiGui;

			case SiGuiType.BULKY_ENTRY:
				declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'));
				const bulkyEntrySiGui = new BulkyEntrySiGui(declaration);

				bulkyEntrySiGui.entry = new SiEntryFactory(declaration, this.injector)
						.createEntry(dataExtr.reqObject('entry'));

				compEssentialsFactory = new SiControlFactory(bulkyEntrySiGui, this.injector);
				bulkyEntrySiGui.controls = compEssentialsFactory.createControls(dataExtr.reqArray('controls'));

				return bulkyEntrySiGui;

			case SiGuiType.COMPACT_ENTRY:
				declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'));
				const compactEntrySiGui = new CompactEntrySiGui(declaration);

				compEssentialsFactory = new SiControlFactory(compactEntrySiGui, this.injector);
				compactEntrySiGui.controls = compEssentialsFactory.createControls(dataExtr.reqArray('controls'));
				compactEntrySiGui.entry = new SiEntryFactory(declaration, this.injector)
						.createEntry(dataExtr.reqObject('entry'));
				return compactEntrySiGui;

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
				this.buildGui(extr.reqObject('content'), SiGuiType.BULKY_ENTRY) as BulkyEntrySiGui,
				this.buildGui(extr.nullaObject('summaryContent'), SiGuiType.COMPACT_ENTRY) as CompactEntrySiGui);
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
		panel.gridPos = SiGuiFactory.buildGridPos(extr.nullaObject('gridPos'));
		panel.allowedTypeIds = extr.nullaStringArray('allowedTypeIds');

		return panel;
	}
}
