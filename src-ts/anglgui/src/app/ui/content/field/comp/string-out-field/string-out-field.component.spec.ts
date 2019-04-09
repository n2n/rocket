import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { StringOutFieldComponent } from './string-out-field.component';

describe('StringOutFieldComponent', () => {
  let component: StringOutFieldComponent;
  let fixture: ComponentFixture<StringOutFieldComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ StringOutFieldComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(StringOutFieldComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
