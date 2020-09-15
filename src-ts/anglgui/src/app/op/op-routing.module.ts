import { NgModule } from '@angular/core';
import { RouterModule, Routes, UrlSegment, UrlMatchResult, UrlSegmentGroup, Route } from '@angular/router';
import { EiComponent } from 'src/app/op/comp/ei/ei.component';
import { FallbackComponent } from 'src/app/op/comp/common/fallback/fallback.component';
import { UsersComponent } from './user/comp/users/users.component';
import { UserComponent } from './user/comp/user/user.component';

const routes: Routes = [
	{
		path: 'users', component: UsersComponent
	},
	{
		path: 'users/detail/:userId', component: UserComponent
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

