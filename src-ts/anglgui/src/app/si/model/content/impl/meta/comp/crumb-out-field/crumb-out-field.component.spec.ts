import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CrumbOutFieldComponent } from './crumb-out-field.component';

describe('CrumbOutFieldComponent', () => {
  let component: CrumbOutFieldComponent;
  let fixture: ComponentFixture<CrumbOutFieldComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CrumbOutFieldComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CrumbOutFieldComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
