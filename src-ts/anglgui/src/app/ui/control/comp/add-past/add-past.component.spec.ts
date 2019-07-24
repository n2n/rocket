import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AddPastComponent } from './add-past.component';

describe('AddPastComponent', () => {
  let component: AddPastComponent;
  let fixture: ComponentFixture<AddPastComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AddPastComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AddPastComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
