import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { PlainContentComponent } from './plain-content.component';

describe('PlainContentComponent', () => {
  let component: PlainContentComponent;
  let fixture: ComponentFixture<PlainContentComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ PlainContentComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(PlainContentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
