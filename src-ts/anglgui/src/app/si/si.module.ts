import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ButtonControlComponent } from './model/control/impl/comp/button-control/button-control.component';
import { CompactExplorerComponent } from './model/gui/impl/comp/compact-explorer/compact-explorer.component';
import { BulkyEntryComponent } from './model/gui/impl/comp/bulky-entry/bulky-entry.component';
import { InputInFieldComponent } from './model/content/impl/alphanum/comp/input-in-field/input-in-field.component';
import { TextareaInFieldComponent } from './model/content/impl/alphanum/comp/textarea-in-field/textarea-in-field.component';
import { FileInFieldComponent } from './model/content/impl/file/comp/file-in-field/file-in-field.component';
import { FileOutFieldComponent } from './model/content/impl/file/comp/file-out-field/file-out-field.component';
import {
	QualifierSelectInFieldComponent
} from './model/content/impl/qualifier/comp/qualifier-select-in-field/qualifier-select-in-field.component';
import { LinkOutFieldComponent } from './model/content/impl/alphanum/comp/link-out-field/link-out-field.component';
import { StringOutFieldComponent } from './model/content/impl/alphanum/comp/string-out-field/string-out-field.component';
import { EmbeddedEntriesInComponent } from './model/content/impl/embedded/comp/embedded-entries-in/embedded-entries-in.component';
import { CompactEntryComponent } from './model/gui/impl/comp/compact-entry/compact-entry.component';
import {
	EmbeddedEntriesSummaryInComponent
} from './model/content/impl/embedded/comp/embedded-entries-summary-in/embedded-entries-summary-in.component';
import { EmbeddedEntriesOutComponent } from './model/content/impl/embedded/comp/embedded-entries-out/embedded-entries-out.component';
import {
	EmbeddedEntriesSummaryOutComponent
} from './model/content/impl/embedded/comp/embedded-entries-summary-out/embedded-entries-summary-out.component';
import {
	EmbeddedEntryPanelsInComponent
} from './model/content/impl/embedded/comp/embedded-entry-panels-in/embedded-entry-panels-in.component';
import { ImageResizeComponent } from './model/content/impl/file/comp/image-resize/image-resize.component';
import { EntryDirective } from './model/mod/directive/entry.directive';
import { FormsModule } from '@angular/forms';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { UiModule } from '../ui/ui.module';
import { UtilModule } from '../util/util.module';
import { PaginationComponent } from './model/gui/impl/comp/pagination/pagination.component';
import { CrumbGroupComponent } from './model/content/impl/meta/comp/crumb-group/crumb-group.component';
import { RouterModule } from '@angular/router';
import { AddPasteComponent } from './model/content/impl/embedded/comp/add-paste/add-paste.component';
import { QualifierComponent } from './model/content/impl/qualifier/comp/qualifier/qualifier.component';
import { TogglerInFieldComponent } from './model/content/impl/boolean/comp/toggler-in-field/toggler-in-field.component';
import { SplitComponent } from './model/content/impl/split/comp/split/split.component';
import { SplitViewMenuComponent } from './model/content/impl/split/comp/split-view-menu/split-view-menu.component';
import { SplitManagerComponent } from './model/content/impl/split/comp/split-manager/split-manager.component';
import { EnumInComponent } from './model/content/impl/enum/comp/enum-in/enum-in.component';
import { QualifierTilingComponent } from './model/content/impl/qualifier/comp/qualifier-tiling/qualifier-tiling.component';
import { ChoosePasteComponent } from './model/content/impl/embedded/comp/choose-paste/choose-paste.component';
import { EmbeddedEntryComponent } from './model/content/impl/embedded/comp/embedded-entry/embedded-entry.component';
import { ImageEditorComponent } from './model/content/impl/file/comp/image-editor/image-editor.component';
import { UploadResultMessageComponent } from './model/content/impl/file/comp/inc/upload-result-message/upload-result-message.component';
import { ImagePreviewComponent } from './model/content/impl/file/comp/image-preview/image-preview.component';
import { CkeInFieldComponent } from './model/content/impl/alphanum/comp/cke-in-field/cke-in-field.component';
import { CKEditorModule } from '@ckeditor/ckeditor5-angular';
import { FieldMessagesComponent } from './model/content/impl/common/comp/field-messages/field-messages.component';
import { CrumbOutFieldComponent } from './model/content/impl/meta/comp/crumb-out-field/crumb-out-field.component';
import { SelectInFieldComponent } from './model/content/impl/alphanum/comp/select-in-field/select-in-field.component';

@NgModule({
	declarations: [
		ButtonControlComponent, CompactExplorerComponent, BulkyEntryComponent, StringOutFieldComponent,
		InputInFieldComponent, TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent,
		QualifierSelectInFieldComponent, LinkOutFieldComponent, CompactEntryComponent, EmbeddedEntriesInComponent,
		EmbeddedEntriesSummaryInComponent, EmbeddedEntriesOutComponent, EmbeddedEntriesSummaryOutComponent,
		ImageResizeComponent, EmbeddedEntryPanelsInComponent, EntryDirective, PaginationComponent, CrumbGroupComponent,
		AddPasteComponent, QualifierComponent, TogglerInFieldComponent, SplitComponent, SplitViewMenuComponent,
		SplitManagerComponent, EnumInComponent, QualifierTilingComponent, ChoosePasteComponent, EmbeddedEntryComponent,
		ImageEditorComponent, UploadResultMessageComponent, ImagePreviewComponent, CkeInFieldComponent, FieldMessagesComponent,
		CrumbOutFieldComponent, SelectInFieldComponent
	],
	imports: [
		CommonModule,
		FormsModule,
		DragDropModule,
		UiModule,
		UtilModule,
		RouterModule,
		CKEditorModule
	],
	exports: [
		CompactExplorerComponent, BulkyEntryComponent, StringOutFieldComponent, InputInFieldComponent,
		TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent, QualifierSelectInFieldComponent,
		LinkOutFieldComponent, CompactEntryComponent, EmbeddedEntriesInComponent, EmbeddedEntriesSummaryInComponent,
		EmbeddedEntriesOutComponent, EmbeddedEntriesSummaryOutComponent, ImageResizeComponent,
		EmbeddedEntryPanelsInComponent, ButtonControlComponent, PaginationComponent, SelectInFieldComponent
	],
	entryComponents: [
		CompactExplorerComponent, BulkyEntryComponent, StringOutFieldComponent,
		InputInFieldComponent, TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent, QualifierSelectInFieldComponent,
		LinkOutFieldComponent, CompactEntryComponent, EmbeddedEntriesInComponent, EmbeddedEntriesSummaryInComponent,
		EmbeddedEntriesOutComponent, EmbeddedEntriesSummaryOutComponent, ImageResizeComponent,
		EmbeddedEntryPanelsInComponent, ButtonControlComponent, PaginationComponent, TogglerInFieldComponent, SplitComponent,
		SplitViewMenuComponent, CrumbGroupComponent, SplitManagerComponent, EnumInComponent, EmbeddedEntryComponent,
		ImageEditorComponent, CkeInFieldComponent, CrumbOutFieldComponent, SelectInFieldComponent
	]
})
export class SiModule { }
