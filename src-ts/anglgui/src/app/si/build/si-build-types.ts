import { SiUiService } from '../manage/si-ui.service';
import { FileInSiField } from '../model/content/impl/file/model/file-in-si-field';
import { LinkOutSiField } from '../model/content/impl/alphanum/model/link-out-si-field';
import { SiService } from '../manage/si.service';
import { SiNavPoint } from '../model/control/si-nav-point';
import { QualifierSelectInSiField } from '../model/content/impl/qualifier/model/qualifier-select-in-si-field';
import { SiEntryFactory } from './si-entry-factory';
import { SiGuiFactory } from './si-gui-factory';

export class SiBuildTypes {
	static SiUiService: new (...args: any[]) => SiUiService;
	static SiService: new (...args: any[]) => SiService;
	static FileInSiField: new (...args: any[]) => FileInSiField;
	static LinkOutSiField: new (...args: any[]) => LinkOutSiField;
	static SiNavPoint: new (...args: any[]) => SiNavPoint;
	static QualifierSelectInSiField: new (...args: any[]) => QualifierSelectInSiField;
	static SiGuiFactory: new (...args: any[]) => SiGuiFactory;
	static SiEntryFactory: new (...args: any[]) => SiEntryFactory;
}
