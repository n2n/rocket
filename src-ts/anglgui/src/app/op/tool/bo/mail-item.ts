import {MailItemAttachment} from "./mail-item-attachment";

export class MailItem {
  dateTime: string;
  to: string;
  from: string;
  cc: string;
  bcc: string;
  replyTo: string;
  attachments: MailItemAttachment[]
  message: string;
  subject: string;
}
