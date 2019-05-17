import { FileFieldModel } from "src/app/ui/content/field/file-field-model";

export interface FileInFieldModel extends FileFieldModel {

	getMimeTypes(): string[];
	
	removeFile(): void;
	
	uploadFile(file: File): void;
}