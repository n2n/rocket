import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EiComponent } from './comp/ei/ei.component';
import { OpRoutingModule } from "src/app/op/op-routing.module";
import { ZoneDirective } from './comp/ei/zone.directive';
import { UiModule } from "src/app/ui/ui.module";
import { FallbackComponent } from "src/app/op/comp/common/fallback/fallback.component";
import { BrowserModule } from "@angular/platform-browser";
import { HttpClientModule } from "@angular/common/http";

@NgModule({
  declarations: [EiComponent, ZoneDirective, FallbackComponent],
  imports: [
    CommonModule,
    OpRoutingModule,
    UiModule,
    HttpClientModule
  ]
})
export class OpModule { }
