
import { SiEntryError } from "src/app/si/model/input/si-entry-error";
import { SiButton } from "src/app/si/model/control/impl/model/si-button";

export class SiResult {
	public directive: string|null = null;
	public ref: string|null = null;
	public href: string|null = null;
	public messages: SiMessage[] = [];
	public entryErrors = new Map<string, SiEntryError>();
	public newButton: SiButton|null = null;
}

type Directive = 'redirectBack' | 'redirect';

interface SiMessage {
	text: string;
	severity: string;
}