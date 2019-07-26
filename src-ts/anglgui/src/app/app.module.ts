import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { OpModule } from 'src/app/op/op.module';

@NgModule({
  declarations: [
	AppComponent
  ],
  imports: [
	BrowserModule,
	AppRoutingModule,
	OpModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
