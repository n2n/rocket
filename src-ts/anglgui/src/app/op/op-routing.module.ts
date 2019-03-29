import { NgModule } from '@angular/core';
import { RouterModule, Routes, UrlSegment, UrlMatchResult, UrlSegmentGroup, Route } from '@angular/router';
import { EiComponent } from "src/app/op/comp/ei/ei.component";
import { FallbackComponent } from "src/app/op/comp/common/fallback/fallback.component";

const routes: Routes = [
    {
        /*path: 'manage', */component: EiComponent, matcher: matchesManageUrl
    },
    {
        path: '**', component: FallbackComponent
    }
];

@NgModule({
    imports: [ RouterModule.forRoot(routes/*, { enableTracing: true }*/) ],
    exports: [ RouterModule ],
    providers: [  ]
})
export class OpRoutingModule {}


export function matchesManageUrl(url: UrlSegment[], group: UrlSegmentGroup, route: Route): UrlMatchResult {
    if (url.length < 1 || url[0].path != 'manage') {
        return null;
    }
    
    return { consumed: url };
}

