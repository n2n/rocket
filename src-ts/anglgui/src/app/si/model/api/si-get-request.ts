import { SiGetInstruction } from "src/app/si/model/api/si-get-instruction";

export class SiGetRequest {
	public getInstructions: SiGetInstruction[];
	
	constructor(...getInstructions: SiGetInstruction[]) {
		this.getInstructions = getInstructions;
	}
}
