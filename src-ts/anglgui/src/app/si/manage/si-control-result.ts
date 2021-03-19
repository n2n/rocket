
import { SiButton } from 'src/app/si/model/control/impl/model/si-button';
import { SiModEvent } from '../model/mod/model/si-mod-state.service';
import { Message } from 'src/app/util/i18n/message';
import { SiNavPoint } from '../model/control/si-nav-point';
import { SiEntry } from '../model/content/si-entry';

export class SiControlResult {
	public directive: SiDirective|null = null;
	public navPoint: SiNavPoint|null = null;
	public messages: Message[] = [];
	public errorEntries = new Map<string, SiEntry>();
	public newButton: SiButton|null = null;
	public modEvent: SiModEvent|null = null;
}

export enum SiDirective {
	REDIRECT_BACK = 'redirectBack',
	REDIRECT = 'redirect'
}
