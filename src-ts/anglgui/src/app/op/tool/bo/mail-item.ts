import {MailItemAttachment} from './mail-item-attachment';

export class MailItem {
	contentVisible = false;
	dateTime: string|null = null;
	to: string|null = null
	from: string|null = null
	cc: string|null = null
	bcc: string|null = null
	replyTo: string|null = null
	attachments: MailItemAttachment[] = [];
	message: string|null = null
	subject: string|null = null

	toggleVisibility(): void {
		this.contentVisible = !this.contentVisible;
	}
}
