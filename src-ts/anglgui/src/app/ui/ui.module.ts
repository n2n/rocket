import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ListZoneComponent } from './structure/comp/list-zone/list-zone.component';
import { LayerComponent } from './structure/comp/layer/layer.component';
import { ContainerComponent } from './structure/comp/container/container.component';

@NgModule({
  declarations: [ ListZoneComponent, LayerComponent, ContainerComponent ],
  imports: [
    CommonModule
  ],
  exports: [
    ContainerComponent,
    LayerComponent,
    ListZoneComponent
  ],
  entryComponents: [ ListZoneComponent ]
})
export class UiModule { }
