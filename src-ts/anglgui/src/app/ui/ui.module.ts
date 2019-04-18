import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { LayerComponent } from './structure/comp/layer/layer.component';
import { ContainerComponent } from './structure/comp/container/container.component';
import { ZoneComponent } from './structure/comp/zone/zone.component';
import { ListZoneContentComponent } from './content/zone/comp/list-zone-content/list-zone-content.component';
import { ZoneContentDirective } from "src/app/ui/structure/comp/zone/zone-content.directive";
import { PaginationComponent } from './content/list/comp/pagination/pagination.component';
import { EntryDirective } from './structure/directive/entry.directive';
import { FieldDirective } from './structure/directive/field.directive';
import { StringOutFieldComponent } from './content/field/comp/string-out-field/string-out-field.component';
import { ControlComponent } from './control/comp/control/control.component';
import { DlZoneContentComponent } from './content/zone/comp/dl-zone-content/dl-zone-content.component';
import { FieldStructureComponent } from './structure/comp/field-structure/field-structure.component';
import { InputInFieldComponent } from './content/field/comp/input-in-field/input-in-field.component';
import { TextareaInFieldComponent } from './content/field/comp/textarea-in-field/textarea-in-field.component';

@NgModule({
  declarations: [ LayerComponent, ContainerComponent, ZoneComponent, ZoneContentDirective, ListZoneContentComponent, 
    PaginationComponent, EntryDirective, FieldDirective, StringOutFieldComponent, ControlComponent, DlZoneContentComponent, 
    FieldStructureComponent, InputInFieldComponent, TextareaInFieldComponent ],
  imports: [
    CommonModule,
    FormsModule
  ],
  exports: [
    ContainerComponent,
    LayerComponent,
    ControlComponent
  ],
  entryComponents: [ ListZoneContentComponent, DlZoneContentComponent, StringOutFieldComponent, InputInFieldComponent,
    TextareaInFieldComponent ]
})
export class UiModule { }
