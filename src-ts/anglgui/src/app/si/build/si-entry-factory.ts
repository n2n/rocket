import { SiDeclaration } from '../model/meta/si-declaration';
import { SiPartialContent } from '../model/content/si-partial-content';
import { SiValueBoundary } from '../model/content/si-value-boundary';
import { SiEntryIdentifier, SiEntryQualifier } from '../model/content/si-entry-qualifier';
import { SiEntry } from '../model/content/si-entry';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiControlFactory } from './si-control-factory';
import { Injector } from '@angular/core';
import { SiControlBoundry } from '../model/control/si-control-bountry';
import { SimpleSiControlBoundry } from '../model/control/impl/model/simple-si-control-boundry';
import { SiMetaFactory } from './si-meta-factory';
import { SiBuildTypes } from './si-build-types';
import { Message } from '../../util/i18n/message';

export class SiEntryFactory {
	constructor(private declaration: SiDeclaration, private injector: Injector) {
	}

	createPartialContent(data: any): SiPartialContent {
		const extr = new Extractor(data);
		return {
			entries: this.createEntries(extr.reqArray('siValueBoundary')),
			count: extr.reqNumber('count'),
			offset: extr.reqNumber('offset')
		};
	}

	createEntries(data: Array<any>): SiValueBoundary[] {
		const entries: Array<SiValueBoundary> = [];
		for (const entryData of data) {
			entries.push(this.createValueBoundary(entryData));
		}

		return entries;
	}

	createValueBoundary(entryData: any): SiValueBoundary {
		const extr = new Extractor(entryData);

		const siValueBoundary = new SiValueBoundary(
				// SiMetaFactory.createEntryIdentifier(extr.reqObject('identifier')),
				SiMetaFactory.createStyle(extr.reqObject('style')));
		siValueBoundary.treeLevel = extr.nullaNumber('treeLevel');

		const controlBoundry = new SimpleSiControlBoundry([siValueBoundary], this.declaration);
		for (const [maskId, entryData] of extr.reqMap('entries')) {
			siValueBoundary.addEntry(this.createEntry(maskId, entryData, controlBoundry));
		}

		siValueBoundary.selectedMaskId = extr.nullaString('selectedMaskId');

		return siValueBoundary;
	}

	private createEntry(maskId: string, data: any, controlBoundary: SiControlBoundry): SiEntry {
		const extr = new Extractor(data);

		const maskDeclaration = this.declaration.getMaskDeclarationByMaskId(maskId);
		const entryQualifier = new SiEntryQualifier(maskDeclaration.mask.qualifier,
				new SiEntryIdentifier(maskDeclaration.mask.qualifier.identifier.typeId, extr.nullaString('id')),
				extr.nullaString('idName'));

		const entry = new SiEntry(entryQualifier);
		entry.fieldMap = new SiBuildTypes.SiFieldFactory(controlBoundary, maskDeclaration.mask, this.injector)
				.createFieldMap(extr.reqMap('fieldMap'));
		entry.controls = new SiControlFactory(controlBoundary, this.injector)
				.createControls(extr.reqArray('controls'));
		entry.messages = Message.createTexts(extr.reqStringArray('messages'));

		return entry;
	}
}
