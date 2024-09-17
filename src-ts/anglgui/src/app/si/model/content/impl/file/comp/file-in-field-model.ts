import { FileFieldModel } from './file-field-model';
import { SiFile } from '../model/file';

export interface FileInFieldModel extends FileFieldModel {

	getApiUrl(): string;

	getMaskId(): string;

	getEntryId(): string|null;

	getFieldName(): string;

	getAcceptedExtensions(): string[];

	getAcceptedMimeTypes(): string[];

	getMaxSize(): number;

	setSiFile(file: SiFile|null): void;
}
