import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LayerComponent } from './structure/comp/layer/layer.component';
import { ContainerComponent } from './structure/comp/container/container.component';
import { ZoneComponent } from './structure/comp/zone/zone.component';
import { ListZoneContentComponent } from './content/zone/comp/list-zone-content/list-zone-content.component';
import { ZoneContentDirective } from "src/app/ui/structure/comp/zone/zone-content.directive";
import { PaginationComponent } from './content/list/comp/pagination/pagination.component';

@NgModule({
  declarations: [ LayerComponent, ContainerComponent, ZoneComponent, ZoneContentDirective, ListZoneContentComponent, PaginationComponent ],
  imports: [
    CommonModule
  ],
  exports: [
    ContainerComponent,
    LayerComponent,
  ],
  entryComponents: [ ListZoneContentComponent ]
})
export class UiModule { }
