import { SiFile } from '../model/file-in-si-field';
import { MessageFieldModel } from '../../common/comp/message-field-model';

export interface ImageEditorModel extends MessageFieldModel {
	setSiFile(siFile: SiFile): void;

	getSiFile(): SiFile;

	upload(blob: Blob, fileName: string|null): Promise<UploadResult>;
}

export interface UploadResult {
	uploadTooLarge?: boolean;
	uploadErrorMessage?: string;
	siFile?: SiFile;
}
