import { MessageFieldModel } from '../../common/comp/message-field-model';

export interface IframeInModel extends MessageFieldModel {
  getSrcDoc(): string|null;
  getFormData(): Map<string, string>|null;
  setFormData(formData: Map<string, string>): void
}
