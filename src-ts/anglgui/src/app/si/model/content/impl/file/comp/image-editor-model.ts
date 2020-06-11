import { SiFile } from '../model/file-in-si-field';

export interface ImageEditorModel {

	getSiFile(): SiFile;

	upload(blob: Blob): UploadResult;
}

export interface UploadResult {
	uploadTooLarge?: boolean;
	uploadErrorMessage?: string;
	siFile?: SiFile;
}
