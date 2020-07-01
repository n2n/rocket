import { SiDeclaration } from '../model/meta/si-declaration';
import { SiPartialContent } from '../model/content/si-partial-content';
import { SiEntry } from '../model/content/si-entry';
import { SiEntryIdentifier, SiEntryQualifier } from '../model/content/si-entry-qualifier';
import { SiEntryBuildup } from '../model/content/si-entry-buildup';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiControlFactory } from './si-control-factory';
import { SiFieldFactory } from './si-field-factory';
import { Injector } from '@angular/core';
import { SiCompFactory } from './si-comp-factory';
import { SiControlBoundry } from '../model/control/si-control-bountry';
import { SimpleSiControlBoundry } from '../model/control/impl/model/simple-si-control-boundry';

export class SiEntryFactory {
	constructor(private declaration: SiDeclaration, private injector: Injector) {
	}

	createPartialContent(data: any): SiPartialContent {
		const extr = new Extractor(data);
		return {
			entries: this.createEntries(extr.reqArray('entries')),
			count: extr.reqNumber('count'),
			offset: extr.reqNumber('offset')
		};
	}

	createEntries(data: Array<any>): SiEntry[] {
		const entries: Array<SiEntry> = [];
		for (const entryData of data) {
			entries.push(this.createEntry(entryData));
		}

		return entries;
	}

	createEntry(entryData: any): SiEntry {
		const extr = new Extractor(entryData);

		const siEntry = new SiEntry(SiCompFactory.createEntryIdentifier(extr.reqObject('identifier')));
		siEntry.treeLevel = extr.nullaNumber('treeLevel');
		siEntry.bulky = extr.reqBoolean('bulky');
		siEntry.readOnly = extr.reqBoolean('readOnly');

		const controlBoundry = new SimpleSiControlBoundry([siEntry]);
		for (const [, buildupData] of extr.reqMap('buildups')) {
			siEntry.addEntryBuildup(this.createEntryBuildup(buildupData, siEntry.identifier, controlBoundry));
		}

		siEntry.selectedTypeId = extr.nullaString('selectedTypeId');

		return siEntry;
	}

	private createEntryBuildup(data: any, identifier: SiEntryIdentifier, controlBoundry: SiControlBoundry): SiEntryBuildup {
		const extr = new Extractor(data);

		const maskDeclaration = this.declaration.getTypeDeclarationByTypeId(extr.reqString('typeId'));
		const entryQualifier = new SiEntryQualifier(maskDeclaration.type.qualifier, identifier, extr.nullaString('idName'));

		const entryBuildup = new SiEntryBuildup(entryQualifier);
		entryBuildup.fieldMap = new SiFieldFactory(controlBoundry, this.declaration, maskDeclaration.type, this.injector)
				.createFieldMap(extr.reqMap('fieldMap'));
		entryBuildup.controls = new SiControlFactory(controlBoundry, this.injector)
				.createControls(extr.reqArray('controls'));

		return entryBuildup;
	}
}
