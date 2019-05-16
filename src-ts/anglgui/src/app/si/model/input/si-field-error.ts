import { SiEntryError } from "src/app/si/model/input/si-entry-error";
import { Message } from "@angular/compiler/src/i18n/i18n_ast";

export class SiFieldError {
	constructor (public messages: string[] = []) {
	}
	
	public subEntryErrors = new Map<string, SiEntryError>();
	
	getAllMessages(): string[] {
		const messages: string[] = [];
		
		messages.push(...this.messages);
		
		for (const [key, entryError] of this.subEntryErrors) {
			messages.push(...entryError.getAllMessages());
		}
		
		return messages;
	}
}