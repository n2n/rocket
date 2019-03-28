import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ListZoneComponent } from './zone/comp/list-zone/list-zone.component';

@NgModule({
  declarations: [ ListZoneComponent ],
  imports: [
    CommonModule
  ],
  exports: [
    ListZoneComponent
  ],
  entryComponents: [ ListZoneComponent ]
})
export class UiModule { }
