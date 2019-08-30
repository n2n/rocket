import { FileFieldModel } from 'src/app/ui/content/field/file-field-model';
import { SiFile } from 'src/app/si/model/content/impl/file-in-si-field';

export interface FileInFieldModel extends FileFieldModel {

	getApiUrl(): string;

	getApiCallId(): object;

	getMimeTypes(): string[];

	setSiFile(file: SiFile|null): void;
}
