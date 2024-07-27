export class SiFrame {
	public sortable = false;
	public treeMode = false;

	constructor(public apiUrl: string/*, public typeContext: SiTypeContext*/) {
	}

	// getApiUrl(apiSection: SiFrameApiSection): string {
	// 	if (this.apiUrl.has(apiSection)) {
	// 		return this.apiUrl.get(apiSection)!;
	// 	}
	//
	// 	throw new IllegalSiStateError('No api url given for section: ' + apiSection)
	// }
}

// export enum SiFrameApiSection {
// 	CONTROL = 'execcontrol',
// 	FIELD = 'callfield',
// 	GET = 'get',
// 	VAL = 'val',
// 	SORT = 'sort'
// }
