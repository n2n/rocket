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
			entries.push(this.createEntry(entryData));
		}

		return entries;
	}

	createEntry(entryData: any): SiValueBoundary {
		const extr = new Extractor(entryData);

		const siValueBoundary = new SiValueBoundary(SiMetaFactory.createEntryIdentifier(extr.reqObject('identifier')),
				SiMetaFactory.createStyle(extr.reqObject('style')));
		siValueBoundary.treeLevel = extr.nullaNumber('treeLevel');

		const controlBoundry = new SimpleSiControlBoundry([siValueBoundary], this.declaration);
		for (const [maskId, buildupData] of extr.reqMap('buildups')) {
			siValueBoundary.addEntry(this.createEntry(maskId, buildupData, siValueBoundary.identifier, controlBoundry));
		}

		siValueBoundary.selectedMaskId = extr.nullaString('selectedMaskId');

		return siValueBoundary;
	}

	private createEntry(maskId: string, data: any, identifier: SiEntryIdentifier, controlBoundry: SiControlBoundry): SiEntry {
		const extr = new Extractor(data);

		const maskDeclaration = this.declaration.getMaskDeclarationByMaskId(maskId);
		const entryQualifier = new SiEntryQualifier(maskDeclaration.mask.qualifier, identifier, extr.nullaString('idName'));

		const entry = new SiEntry(entryQualifier);
		entry.fieldMap = new SiBuildTypes.SiFieldFactory(controlBoundry, maskDeclaration.mask, this.injector)
				.createFieldMap(extr.reqMap('fieldMap'));
		entry.controls = new SiControlFactory(controlBoundry, this.injector)
				.createControls(extr.reqArray('controls'));

		return entry;
	}
}
