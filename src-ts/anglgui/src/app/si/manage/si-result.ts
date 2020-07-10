
import { SiEntryError } from 'src/app/si/model/input/si-entry-error';
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { UiNavPoint } from 'src/app/ui/util/model/ui-nav-point';

export class SiResult {
	public directive: SiDirective|null = null;
	public navPoint: UiNavPoint|null = null;
	public messages: SiMessage[] = [];
	public entryErrors = new Map<string, SiEntryError>();
	public newButton: SiButton|null = null;
}

export enum SiDirective {
	REDIRECT_BACK = 'redirectBack',
	REDIRECT = 'redirect'
}

interface SiMessage {
	text: string;
	severity: string;
}
