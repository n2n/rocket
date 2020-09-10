import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { UsersComponentComponent } from './users-component.component';

describe('UsersComponentComponent', () => {
let component: UsersComponentComponent;
let fixture: ComponentFixture<UsersComponentComponent>;

beforeEach(async(() => {
TestBed.configureTestingModule({
declarations: [ UsersComponentComponent ]
})
.compileComponents();
}));

beforeEach(() => {
fixture = TestBed.createComponent(UsersComponentComponent);
component = fixture.componentInstance;
fixture.detectChanges();
});

it('should create', () => {
expect(component).toBeTruthy();
});
});
