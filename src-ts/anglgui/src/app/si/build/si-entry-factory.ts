import { SiDeclaration } from '../model/meta/si-declaration';
import { SiPartialContent } from '../model/content/si-partial-content';
import { SiEntry } from '../model/content/si-entry';
import { SiEntryIdentifier, SiEntryQualifier } from '../model/content/si-qualifier';
import { SiEntryBuildup } from '../model/content/si-entry-buildup';
import { Extractor } from 'src/app/util/mapping/extractor';
import { SiContentFactory } from './si-content-factory';
import { SiComp } from '../model/comp/si-comp';
import { SiCompEssentialsFactory } from './si-comp-essentials-factory';
import { SiFieldFactory } from './si-field-factory';

export class SiEntryFactory {
	constructor(private comp: SiComp, private declaration: SiDeclaration) {
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

		const siEntry = new SiEntry(SiContentFactory.createEntryIdentifier(extr.reqObject('identifier')));
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
		entryBuildup.fieldMap = new SiFieldFactory(entryBuildup).createFieldMap(extr.reqMap('fieldMap'));
		entryBuildup.controls = new SiCompEssentialsFactory(this.comp).createControls(extr.reqArray('controls'));

		return entryBuildup;
	}
}
