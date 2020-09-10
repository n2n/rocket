import { NgModule } from '@angular/core';
import { RouterModule, Routes, UrlSegment, UrlMatchResult, UrlSegmentGroup, Route } from '@angular/router';
import { EiComponent } from 'src/app/op/comp/ei/ei.component';
import { FallbackComponent } from 'src/app/op/comp/common/fallback/fallback.component';
import { UsersComponentComponent } from './user/comp/users-component/users-component.component';

const routes: Routes = [
	{
		path: 'users', component: UsersComponentComponent
	},
	{
		/*path: 'manage', */component: EiComponent, matcher: matchesManageUrl
	},
	{
		path: '**', component: FallbackComponent
	}
];

@NgModule({
	imports: [ RouterModule.forRoot(routes/*, { enableTracing: true }*/)],
	exports: [ RouterModule ],
	providers: [	]
})
export class OpRoutingModule {}


export function matchesManageUrl(url: UrlSegment[], group: UrlSegmentGroup, route: Route): UrlMatchResult {
	if (url.length < 1 || url[0].path !== 'manage') {
		alert('not found');
		return null as any;
	}

	return { consumed: url };
}

