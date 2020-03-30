import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ChoosePasteComponent } from './choose-paste.component';

describe('ChoosePasteComponent', () => {
  let component: ChoosePasteComponent;
  let fixture: ComponentFixture<ChoosePasteComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ChoosePasteComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ChoosePasteComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
