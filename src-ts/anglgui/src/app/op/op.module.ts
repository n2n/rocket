import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EiComponent } from './comp/ei/ei.component';
import { OpRoutingModule } from "src/app/op/op-routing.module";
import { ZoneDirective } from './comp/ei/zone.directive';
import { UiModule } from "src/app/ui/ui.module";
import { FallbackComponent } from "src/app/op/comp/common/fallback/fallback.component";

@NgModule({
  declarations: [EiComponent, ZoneDirective, FallbackComponent],
  imports: [
    CommonModule,
    OpRoutingModule,
    UiModule,
  ]
})
export class OpModule { }
