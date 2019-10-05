import { SiDeclaration } from '../model/meta/si-declaration';
import { SiPartialContent } from '../model/content/si-partial-content';
import { SiEntry } from '../model/content/si-entry';
import { SiEntryIdentifier, SiEntryQualifier } from '../model/content/si-qualifier';
import { SiMetaFactory } from './si-meta-factory';
import { SiEntryBuildup } from '../model/content/si-entry-buildup';
import { Extractor } from 'src/app/util/mapping/extractor';


export class SiEntryFactory {
	constructor (private declaration: SiDeclaration) {
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

		const siEntry = new SiEntry(this.createEntryIdentifier(extr.reqObject('identifier')));
		siEntry.treeLevel = extr.nullaNumber('treeLevel');
		siEntry.bulky = extr.reqBoolean('bulky');
		siEntry.readOnly = extr.reqBoolean('readOnly');

		for (const [, buildupData] of extr.reqMap('buildups')) {
			siEntry.addEntryBuildup(this.createEntryBuildup(buildupData));
		}

		return siEntry;
	}

	createEntryIdentifier(data: any): SiEntryIdentifier {
		const extr = new Extractor(data);

		return new SiEntryIdentifier(SiMetaFactory.createTypeIdentifier('typeIdentifier'), extr.nullaString('id'));
	}

	createEntryQualifier(data: any): SiEntryQualifier {
		const extr = new Extractor(data);

		return new SiEntryQualifier(SiMetaFactory.createTypeQualifier('typeIdentifier'), extr.nullaString('id'),
				extr.nullaString('idName'));
	}

	private createEntryBuildup(data: any): SiEntryBuildup {
		const extr = new Extractor(data);

		return new SiEntryBuildup(this.createEntryQualifier('entryQualifier'),
				extr.nullaString('idName'), this.createFieldMap(extr.reqMap('fields')),
				this.createControlMap(extr.reqMap('controls')));
	}

	private createFieldMap(data: Map<string, any>): Map<string, SiField> {
		const fields = new Map<string, SiField>();

		for (const [fieldId, fieldData] of data) {
			fields.set(fieldId, this.createField(fieldData));
		}
		return fields;
	}
}