


import { SiFieldError } from "src/app/si/model/input/si-field-error";

export class SiEntryError {
	constructor (public messages: string[] = []) {
	}
	
	public fieldErrors = new Map<string, SiFieldError>();
	
	getAllMessages(): string[] {
		const messages: string[] = [];
		
		messages.push(...this.messages);
		
		for (const [key, fieldError] of this.fieldErrors) {
			messages.push(...fieldError.getAllMessages());
		}
		
		return messages;
	}
}