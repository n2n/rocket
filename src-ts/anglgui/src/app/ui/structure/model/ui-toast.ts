import {Message} from "../../../util/i18n/message";

export class UiToast {
  constructor(public message: Message, public durationMs: number) {
  }
}
