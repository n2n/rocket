import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { EmbeddedEntriesInComponent } from './embedded-entries-in.component';

describe('EmbeddedEntriesInComponent', () => {
	let component: EmbeddedEntriesInComponent;
	let fixture: ComponentFixture<EmbeddedEntriesInComponent>;

	beforeEach(async(() => {
	TestBed.configureTestingModule({
		declarations: [ EmbeddedEntriesInComponent ]
	})
	.compileComponents();
	}));

	beforeEach(() => {
	fixture = TestBed.createComponent(EmbeddedEntriesInComponent);
	component = fixture.componentInstance;
	fixture.detectChanges();
	});

	it('should create', () => {
	expect(component).toBeTruthy();
	});
});
