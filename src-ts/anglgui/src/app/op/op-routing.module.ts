import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { EiComponent } from "src/app/op/comp/ei/ei.component";
import { FallbackComponent } from "src/app/op/comp/common/fallback/fallback.component";

const routes: Routes = [
    {
        path: '**', component: EiComponent
    },
    {
        path: '**', component: FallbackComponent
    }
];

@NgModule({
    imports: [ RouterModule.forRoot(routes) ],
    exports: [ RouterModule ],
    providers: [  ]
})
export class OpRoutingModule {}
