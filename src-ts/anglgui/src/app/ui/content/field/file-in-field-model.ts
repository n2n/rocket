import { FileFieldModel } from 'src/app/ui/content/field/file-field-model';
import { SiFile } from 'src/app/si/model/content/impl/file-in-si-field';

export interface FileInFieldModel extends FileFieldModel {

	getApiUrl(): string;

	getApiCallId(): object;

	getAcceptedExtensions(): string[];

	getAcceptedMimeTypes(): string[];

	getMaxSize(): number;

	setSiFile(file: SiFile|null): void;
}
