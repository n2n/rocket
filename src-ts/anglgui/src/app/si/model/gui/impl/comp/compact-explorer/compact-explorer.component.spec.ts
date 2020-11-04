import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CompactExplorerComponent } from './compact-explorer.component';

describe('CompactExplorerComponent', () => {
	let component: CompactExplorerComponent;
	let fixture: ComponentFixture<CompactExplorerComponent>;

	beforeEach(async(() => {
		TestBed.configureTestingModule({
			declarations: [ CompactExplorerComponent ]
		})
		.compileComponents();
	}));

	beforeEach(() => {
		fixture = TestBed.createComponent(CompactExplorerComponent);
		component = fixture.componentInstance;
		fixture.detectChanges();
	});

	it('should create', () => {
		expect(component).toBeTruthy();
	});
});
