import { FileFieldModel } from './file-field-model';
import { SiFile } from '../model/file';

export interface FileInFieldModel extends FileFieldModel {

	getApiFieldUrl(): string;

	getApiCallId(): object;

	getAcceptedExtensions(): string[];

	getAcceptedMimeTypes(): string[];

	getMaxSize(): number;

	setSiFile(file: SiFile|null): void;
}
