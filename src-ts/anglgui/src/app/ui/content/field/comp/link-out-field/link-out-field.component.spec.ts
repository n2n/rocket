import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { LinkOutFieldComponent } from './link-out-field.component';

describe('LinkOutFieldComponent', () => {
  let component: LinkOutFieldComponent;
  let fixture: ComponentFixture<LinkOutFieldComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ LinkOutFieldComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(LinkOutFieldComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
