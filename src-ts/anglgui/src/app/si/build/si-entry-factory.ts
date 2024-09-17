import { SiDeclaration } from '../model/meta/si-declaration';
import { SiPartialContent } from '../model/content/si-partial-content';
import { SiValueBoundary } from '../model/content/si-value-boundary';
import { SiEntry } from '../model/content/si-entry';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiControlFactory } from './si-control-factory';
import { Injector } from '@angular/core';
import { SiControlBoundry } from '../model/control/si-control-boundry';
import { SimpleSiControlBoundry } from '../model/control/impl/model/simple-si-control-boundry';
import { Message } from '../../util/i18n/message';
import { SiMetaFactory } from './si-meta-factory';
import { SiFieldFactory } from './si-field-factory';

export class SiEntryFactory {
	constructor(private declaration: SiDeclaration, private apiUrl: string|null, private injector: Injector) {
	}

	createPartialContent(data: any): SiPartialContent {
		const extr = new Extractor(data);
		return {
			valueBoundaries: this.createEntries(extr.reqArray('siValueBoundaries')),
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
				/*SiMetaFactory.createEntryIdentifier(extr.reqObject('identifier')),
				SiMetaFactory.createStyle(extr.reqObject('style'))*/);
		siValueBoundary.treeLevel = extr.nullaNumber('treeLevel');

		const controlBoundry = new SimpleSiControlBoundry([siValueBoundary], this.declaration, this.apiUrl);
		for (const [maskId, entryData] of extr.reqMap('entries')) {
			siValueBoundary.addEntry(this.createEntry(maskId, entryData, controlBoundry));
		}

		siValueBoundary.selectedMaskId = extr.nullaString('selectedMaskId');

		return siValueBoundary;
	}

	private createEntry(maskId: string, data: any, controlBoundary: SiControlBoundry): SiEntry {
		const extr = new Extractor(data);

		const mask = this.declaration.getMaskById(maskId);
		const entryQualifier = SiMetaFactory.createEntryQualifier(extr.reqObject('qualifier'));

		const entry = new SiEntry(entryQualifier);
		entry.fieldMap = new SiFieldFactory(controlBoundary, mask, entry, this.injector)
				.createFieldMap(extr.reqMap('fieldMap'));
		entry.controls = new SiControlFactory(controlBoundary, this.injector)
				.createControls(maskId, entryQualifier.identifier.id, extr.reqMap('controls'));
		entry.messages = Message.createTexts(extr.reqStringArray('messages'));

		return entry;
	}
}
