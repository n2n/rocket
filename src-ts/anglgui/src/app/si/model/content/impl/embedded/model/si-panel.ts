import { SiEmbeddedEntry } from './si-embedded-entry';
import { EmbeddedEntriesInConfig } from './embe/embedded-entries-config';
import { EmbeInSource } from './embe/embe-collection';
import { Message } from 'src/app/util/i18n/message';

export class SiPanel implements EmbeddedEntriesInConfig, EmbeInSource {
	values: SiEmbeddedEntry[] = [];
	allowedTypeIds: string[]|null = null;
	min = 0;
	max: number|null = null;
	gridPos: SiGridPos|null = null;
	nonNewRemovable = true;
	sortable = false;
	reduced = true;

	private messages = new Array<Message>();

	constructor(public name: string, public label: string) {
	}

	setValues(values: SiEmbeddedEntry[]): void {
		this.values = values;
		this.validate();
	}

	getValues(): SiEmbeddedEntry[] {
		return this.values;
	}

	private validate() {
		this.messages = [];
		const values = this.getTypeSelectedValues();

		if (values.length < this.min) {
			this.messages.push(Message.createCode('min_elements_err',
					new Map([['{field}', this.label], ['{min}', this.min.toString()]])));
		}

		if (this.max !== null && values.length > this.max) {
			this.messages.push(Message.createCode('max_elements_err',
					new Map([['{field}', this.label], ['{max}', this.max.toString()]])));
		}
	}

	private getTypeSelectedValues(): SiEmbeddedEntry[] {
		return this.values.filter(ee => ee.entry.selectedTypeId);
	}

	getMessages(): Message[] {
		return this.messages;
	}

	readInput(): object {
		return {
			name: this.name,
			entryInputs: this.getTypeSelectedValues().map(embe => embe.entry.readInput())
		};
	}
}

export interface SiGridPos {
	colStart: number;
	colEnd: number;
	rowStart: number;
	rowEnd: number;
}
