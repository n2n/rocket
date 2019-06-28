import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { LayerComponent } from './structure/comp/layer/layer.component';
import { ContainerComponent } from './structure/comp/container/container.component';
import { ZoneComponent } from './structure/comp/zone/zone.component';
import { ListZoneContentComponent } from './content/zone/comp/list-zone-content/list-zone-content.component';
import { PaginationComponent } from './content/list/comp/pagination/pagination.component';
import { EntryDirective } from './structure/directive/entry.directive';
import { FieldDirective } from './structure/directive/field.directive';
import { StringOutFieldComponent } from './content/field/comp/string-out-field/string-out-field.component';
import { ControlComponent } from './control/comp/control/control.component';
import { DlZoneContentComponent } from './content/zone/comp/dl-zone-content/dl-zone-content.component';
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

@NgModule({
  declarations: [ LayerComponent, ContainerComponent, ZoneComponent, StructureContentDirective, ListZoneContentComponent, 
    PaginationComponent, EntryDirective, FieldDirective, StringOutFieldComponent, ControlComponent, DlZoneContentComponent, 
    FieldStructureComponent, InputInFieldComponent, TextareaInFieldComponent, StructureComponent, FileInFieldComponent, FileOutFieldComponent, LinkOutFieldComponent, QualifierSelectInFieldComponent ],
  imports: [
    CommonModule,
    FormsModule,
    UtilModule,
    RouterModule
  ],
  exports: [
    ContainerComponent,
    LayerComponent,
    ControlComponent
  ],
  entryComponents: [ ListZoneContentComponent, DlZoneContentComponent, FieldStructureComponent, StringOutFieldComponent, 
    InputInFieldComponent, TextareaInFieldComponent, FileInFieldComponent, FileOutFieldComponent ]
})
export class UiModule { }
