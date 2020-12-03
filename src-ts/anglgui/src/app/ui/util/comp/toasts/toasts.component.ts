import {Component, Input, IterableDiffer, IterableDiffers, OnInit} from '@angular/core';
import {UiToast} from "../../../structure/model/ui-toast";
import {Message} from "../../../../util/i18n/message";

@Component({
  selector: 'rocket-ui-toasts',
  templateUrl: './toasts.component.html',
  styleUrls: ['./toasts.component.css']
})
export class ToastsComponent implements OnInit {

  @Input()
  public messages: Message[] = [];
  @Input()
  public toasts: UiToast[] = [];

  private iterableDiffer: IterableDiffer<UiToast>;

  constructor(private iterableDiffers: IterableDiffers) {
    this.iterableDiffer = iterableDiffers.find([]).create(null);
  }

  ngOnInit(): void {}

  private addToastRemovalTimeout(toast: UiToast) {
    setTimeout(() => this.toasts.splice(this.toasts.indexOf(toast), 1), toast.durationMs);
  }

  ngDoCheck() {
    let changes = this.iterableDiffer.diff(this.toasts);
    if (changes) {
      changes.forEachAddedItem((change) => this.addToastRemovalTimeout(change.item))
    }
  }
}
