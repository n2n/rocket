import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { EiComponent } from "src/app/op/comp/ei/ei.component";

const routes: Routes = [
    {
        path: '**', component: EiComponent
    }
];

@NgModule({
    imports: [ RouterModule.forRoot(routes) ],
    exports: [ RouterModule ],
    providers: [  ]
})
export class OpRoutingModule {}
