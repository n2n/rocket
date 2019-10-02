
export class Message {
	constructor(readonly content: string, readonly translated: boolean) {
	}

	static createText(text: string): Message {
		return new Message(text, true);
	}

	static createTexts(texts: string[]): Message[] {
		return texts.map(text => new Message(text, true));
	}

	static createCode(code: string): Message {
		return new Message(code, false);
	}

	static createCodes(codes: string[]): Message[] {
		return codes.map(code => new Message(code, false));
	}
}
