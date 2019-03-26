import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EiComponent } from './comp/ei/ei.component';
import { OpRoutingModule } from "src/app/op/op-routing.module";

@NgModule({
  declarations: [EiComponent],
  imports: [
    CommonModule,
    OpRoutingModule
  ]
})
export class OpModule { }
