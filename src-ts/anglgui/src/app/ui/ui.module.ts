import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { DragDropModule  } from '@angular/cdk/drag-drop';
import { LayerComponent } from './structure/comp/layer/layer.component';
import { ContainerComponent } from './structure/comp/container/container.component';
import { ZoneComponent } from './structure/comp/zone/zone.component';
import { ListZoneContentComponent } from './content/zone/comp/list-zone-content/list-zone-content.component';
import { PaginationComponent } from './content/list/comp/pagination/pagination.component';
import { EntryDirective } from './structure/directive/entry.directive';
import { StringOutFieldComponent } from './content/field/comp/string-out-field/string-out-field.component';
import { ControlComponent } from './control/comp/control/control.component';
import { BulkyEntryComponent } from './content/zone/comp/bulky-entry/bulky-entry.component';
import { InputInFieldComponent } from './content/field/comp/input-in-field/input-in-field.component';
import { TextareaInFieldComponent } from './content/field/comp/textarea-in-field/textarea-in-field.component';
import { StructureComponent } from './structure/comp/structure/structure.component';
import { StructureContentDirective } from "src/app/ui/structure/comp/structure/structure-content.directive";
import { FieldStructureComponent } from "src/app/ui/structure/comp/field-structure/field-structure.component";
import { FileInFieldComponent } from './content/field/comp/file-in-field/file-in-field.component';
import { FileOutFieldComponent } from './content/field/comp/file-out-field/file-out-field.component';
import { UtilModule } from "src/app/util/util.module";
import { LinkOutFieldComponent } from './content/field/comp/link-out-field/link-out-field.component';
import { QualifierSelectInFieldComponent } from './content/field/comp/qualifier-select-in-field/qualifier-select-in-field.component';
import { RouterModule } from "@angular/router";
import { EmbeddedEntryInFieldComponent } from './content/field/comp/embedded-entry-in-field/embedded-entry-in-field.component';
import { CompactEntryComponent } from './content/zone/comp/compact-entry/compact-entry.component';
import { QualifierComponent } from './content/entry/comp/qualifier/qualifier.component';
import { AddPastComponent } from './control/comp/add-past/add-past.component';

@NgModule({
  declarations: [ LayerComponent, ContainerComponent, ZoneComponent, StructureContentDirective, ListZoneContentComponent, 
    PaginationComponent, EntryDirective, StringOutFieldComponent, ControlComponent, BulkyEntryComponent, 
    FieldStructureComponent, InputInFieldComponent, TextareaInFieldComponent, StructureComponent, FileInFieldComponent, 
    FileOutFieldComponent, LinkOutFieldComponent, QualifierSelectInFieldComponent, EmbeddedEntryInFieldComponent, 
    CompactEntryComponent, QualifierComponent, AddCopyControlComponent, AddPastComponent ],
  imports: [
    CommonModule,
    FormsModule,
    UtilModule,
    RouterModule,
    DragDropModule 
  ],
  exports: [
    ContainerComponent,
    LayerComponent,
    ControlComponent
  ],
  entryComponents: [ ListZoneContentComponent, BulkyEntryComponent, FieldStructureComponent, StringOutFieldComponent, 
    InputInFieldComponent, TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent, QualifierSelectInFieldComponent,
    LinkOutFieldComponent, EmbeddedEntryInFieldComponent, CompactEntryComponent
  ]
})
export class UiModule { }
