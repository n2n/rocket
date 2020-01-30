
import { SiEntryError } from "src/app/si/model/input/si-entry-error";
import { SiButton } from "src/app/si/model/control/impl/model/si-button";
import { UiNavPoint } from "src/app/ui/util/model/ui-nav-point";

export class SiResult {
	public directive: string|null = null;
	public navPoint: UiNavPoint|null = null;
	public messages: SiMessage[] = [];
	public entryErrors = new Map<string, SiEntryError>();
	public newButton: SiButton|null = null;
}

type Directive = 'redirectBack' | 'redirect';

interface SiMessage {
	text: string;
	severity: string;
}