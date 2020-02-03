import { SiDeclaration } from '../model/meta/si-declaration';
import { SiPartialContent } from '../model/content/si-partial-content';
import { SiEntry } from '../model/content/si-entry';
import { SiEntryIdentifier, SiEntryQualifier } from '../model/content/si-qualifier';
import { SiEntryBuildup } from '../model/content/si-entry-buildup';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiComp } from '../model/comp/si-comp';
import { SiCompEssentialsFactory } from './si-comp-essentials-factory';
import { SiFieldFactory } from './si-field-factory';
import { Injector } from '@angular/core';
import { SiCompFactory } from './si-comp-factory';

export class SiEntryFactory {
	constructor(private comp: SiComp, private declaration: SiDeclaration, private injector: Injector) {
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

		for (const [, buildupData] of extr.reqMap('buildups')) {
			siEntry.addEntryBuildup(this.createEntryBuildup(buildupData, siEntry.identifier));
		}

		return siEntry;
	}

	private createEntryBuildup(data: any, identifier: SiEntryIdentifier): SiEntryBuildup {
		const extr = new Extractor(data);

		const typeDeclaration = this.declaration.getTypeDeclarationByTypeId(extr.reqString('typeId'));
		const entryQualifier = new SiEntryQualifier(typeDeclaration.type.qualifier, identifier.id, extr.nullaString('idName'));

		const entryBuildup = new SiEntryBuildup(entryQualifier);
		entryBuildup.fieldMap = new SiFieldFactory(this.comp, this.declaration, typeDeclaration.type, this.injector)
				.createFieldMap(extr.reqMap('fieldMap'));
		entryBuildup.controls = new SiCompEssentialsFactory(this.comp, this.injector)
				.createControls(extr.reqArray('controls'));

		return entryBuildup;
	}
}
