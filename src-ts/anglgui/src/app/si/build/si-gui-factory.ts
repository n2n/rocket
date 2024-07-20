import { SiControlFactory } from './si-control-factory';
import { SiDeclaration } from '../model/meta/si-declaration';
import { ObjectMissmatchError, Extractor } from 'src/app/util/mapping/extractor';
import { CompactExplorerSiGui } from '../model/gui/impl/model/compact-explorer-si-gui';
import { SiMetaFactory } from './si-meta-factory';
import { BulkyEntrySiGui } from '../model/gui/impl/model/bulky-entry-si-gui';
import { CompactEntrySiGui } from '../model/gui/impl/model/compact-entry-si-gui';
import { SiGui } from '../model/gui/si-gui';
import { SiEmbeddedEntry } from '../model/content/impl/embedded/model/si-embedded-entry';
import { SiPanel } from '../model/content/impl/embedded/model/si-panel';
import { Injector } from '@angular/core';
import { SiService } from '../manage/si.service';
import { SiModStateService } from '../model/mod/model/si-mod-state.service';
import { IframeSiGui } from '../model/gui/impl/model/iframe-si-gui';
import { SiEssentialsFactory } from './si-field-essentials-factory';
import { SiBuildTypes } from './si-build-types';
import { SiEntryFactory } from './si-entry-factory';
import { SiFrame } from '../model/meta/si-frame';

let SiServiceType: new(...args: any[]) => SiService;
import('../manage/si.service').then(m => {
	SiServiceType = m.SiService;
});

enum SiGuiType {
	COMPACT_EXPLORER = 'compact-explorer',
	BULKY_ENTRY = 'bulky-entry',
	COMPACT_ENTRY = 'compact-entry',
	IFRAME = 'iframe'
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


	buildGui(data: any, requiredType: SiGuiType|null = null): SiGui|null {
		if (!data) {
			return null;
		}

		const extr = new Extractor(data);
		const dataExtr = extr.reqExtractor('data');
		let compEssentialsFactory: SiControlFactory;
		let declaration: SiDeclaration;
		let frame: SiFrame|null;

		const type = extr.reqString('type');

		if (!!requiredType && requiredType !== type) {
			throw new ObjectMissmatchError('Type ' + requiredType + ' required. Given: ' + type);
		}

		switch (type) {
			case SiGuiType.COMPACT_EXPLORER:
				frame = SiMetaFactory.createFrame(dataExtr.reqObject('frame'));
				const compactExplorerSiGui = new CompactExplorerSiGui(dataExtr.reqNumber('pageSize'),
						frame, this.injector.get(SiServiceType),
						this.injector.get(SiModStateService));

				compEssentialsFactory = new SiControlFactory(compactExplorerSiGui.pageCollection, this.injector);
				compactExplorerSiGui.pageCollection.controls = compEssentialsFactory.createControls(dataExtr.reqMap('controls'));
				declaration = compactExplorerSiGui.pageCollection.declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'));

				const partialContentData = dataExtr.nullaObject('partialContent');
				if (partialContentData) {
					compactExplorerSiGui.partialContent = new SiEntryFactory(declaration, frame.apiUrl, this.injector)
							.createPartialContent(partialContentData);
				}

				return compactExplorerSiGui;

			case SiGuiType.BULKY_ENTRY:
				frame = SiMetaFactory.buildFrame(dataExtr.nullaObject('frame'));
				declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'));
				const bulkyEntrySiGui = new BulkyEntrySiGui(
						frame, declaration,
						this.injector.get(SiServiceType), this.injector.get(SiModStateService));

				bulkyEntrySiGui.entryControlsIncluded = dataExtr.reqBoolean('entryControlsIncluded');
				bulkyEntrySiGui.valueBoundary = new SiEntryFactory(declaration, frame?.apiUrl ?? null, this.injector)
						.createValueBoundary(dataExtr.reqObject('valueBoundary'));

				compEssentialsFactory = new SiControlFactory(bulkyEntrySiGui, this.injector);
				bulkyEntrySiGui.controls = compEssentialsFactory.createControls(dataExtr.reqMap('controls'));

				return bulkyEntrySiGui;

			case SiGuiType.COMPACT_ENTRY:
				frame = SiMetaFactory.createFrame(dataExtr.reqObject('frame'));
				declaration = SiMetaFactory.createDeclaration(dataExtr.reqObject('declaration'));
				const compactEntrySiGui = new CompactEntrySiGui(frame,
						declaration, this.injector.get(SiServiceType), this.injector.get(SiModStateService));

				compEssentialsFactory = new SiControlFactory(compactEntrySiGui, this.injector);
				compactEntrySiGui.controls = compEssentialsFactory.createControls(dataExtr.reqMap('controls'));
				compactEntrySiGui.valueBoundary = new SiEntryFactory(declaration, frame.apiUrl, this.injector)
						.createValueBoundary(dataExtr.reqObject('valueBoundary'));
				return compactEntrySiGui;

			case SiGuiType.IFRAME:
				return new IframeSiGui(dataExtr.nullaString('url'), dataExtr.nullaString('srcDoc'));
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

		const panel = new SiPanel(extr.reqString('name'), extr.reqString('label'),
				extr.reqString('bulkyMaskId'), extr.nullaString('summaryMaskId'));
		panel.values = this.createEmbeddedEntries(extr.reqArray('values'));
		panel.min = extr.reqNumber('min');
		panel.max = extr.nullaNumber('max');
		panel.nonNewRemovable = extr.reqBoolean('nonNewRemovable');
		panel.sortable = extr.reqBoolean('sortable');
		panel.gridPos = SiEssentialsFactory.buildGridPos(extr.nullaObject('gridPos'));
		panel.allowedMaskIds = extr.nullaStringArray('allowedMaskIds');

		return panel;
	}
}
