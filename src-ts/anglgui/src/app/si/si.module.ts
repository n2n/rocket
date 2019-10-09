import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ButtonControlComponent } from './model/control/impl/comp/button-control/button-control.component';
import { ListZoneContentComponent } from './model/comp/impl/comp/list-zone-content/list-zone-content.component';
import { BulkyEntryComponent } from './model/comp/impl/comp/bulky-entry/bulky-entry.component';
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
import { CompactEntryComponent } from './model/comp/impl/comp/compact-entry/compact-entry.component';
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
import { EntryDirective } from './model/comp/impl/directive/entry.directive';
import { FormsModule } from '@angular/forms';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { UiModule } from '../ui/ui.module';

@NgModule({
	declarations: [
		ButtonControlComponent, ListZoneContentComponent, BulkyEntryComponent, StringOutFieldComponent,
		InputInFieldComponent, TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent,
		QualifierSelectInFieldComponent, LinkOutFieldComponent, CompactEntryComponent, EmbeddedEntriesInComponent,
		EmbeddedEntriesSummaryInComponent, EmbeddedEntriesOutComponent, EmbeddedEntriesSummaryOutComponent,
		ImageResizeComponent, EmbeddedEntryPanelsInComponent, EntryDirective
	],
	imports: [
		CommonModule,
		FormsModule,
		DragDropModule,
		UiModule
	],
	exports: [
		ListZoneContentComponent, BulkyEntryComponent, StringOutFieldComponent,
		InputInFieldComponent, TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent, QualifierSelectInFieldComponent,
		LinkOutFieldComponent, CompactEntryComponent, EmbeddedEntriesInComponent, EmbeddedEntriesSummaryInComponent,
		EmbeddedEntriesOutComponent, EmbeddedEntriesSummaryOutComponent, ImageResizeComponent, EmbeddedEntryPanelsInComponent
	],
	entryComponents: [
		ListZoneContentComponent, BulkyEntryComponent, StringOutFieldComponent,
		InputInFieldComponent, TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent, QualifierSelectInFieldComponent,
		LinkOutFieldComponent, CompactEntryComponent, EmbeddedEntriesInComponent, EmbeddedEntriesSummaryInComponent,
		EmbeddedEntriesOutComponent, EmbeddedEntriesSummaryOutComponent, ImageResizeComponent, EmbeddedEntryPanelsInComponent
	]
})
export class SiModule { }

