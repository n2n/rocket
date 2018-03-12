namespace Rocket.Impl.Translation {

	export class LoadJobExecuter {
		private groups: LoadJobGroup[] = [];
		
		constructor() {
		}
		
		add(loadJob: LoadJob) {
			for (let group of this.groups) {
				if (group.add(loadJob)) return;
			}
			
			this.groups.push(LoadJobGroup.create(loadJob));
		}
		
		exec() {
			for (let group of this.groups) {
				group.exec();
			}
			this.groups = [];
		}
	}
	
	class LoadJobGroup {
		loadJobs: LoadJob[] = [];
		
		constructor(private url: Jhtml.Url) {
		}
		
		add(loadJob: LoadJob): boolean {
			if (!this.url.equals(loadJob.url)) {
				return false;
			}
			
			this.loadJobs.push(loadJob);
			return true;
		}
		
		exec() {
			let guiIdPaths: string[] = [];
			
			for (let loadJob of this.loadJobs) {
				guiIdPaths.push(loadJob.guiIdPath);
				loadJob.content.loading = true;
			}
			
			let url = this.url.extR(null, { guiIdPaths: guiIdPaths });
			
			Jhtml.lookupModel(url).then((result) => {
				this.splitResult(result.model.snippet);
			});
		}
		
		private splitResult(snippet: Jhtml.Snippet) {
			let usedElements: Element[] = [];
		
			$(snippet.elements).children().each((i, elem) => {
				let elemJq = $(elem);
				let guiIdPath = elemJq.data("rocket-impl-gui-id-path");
				
				let loadJob = this.loadJobs.find(loadJob => loadJob.guiIdPath == guiIdPath);
				let newContentJq = elemJq.children().first();
				
				loadJob.content.replaceField(newContentJq);
				loadJob.content.loading = false;
				usedElements.push(newContentJq.get(0));
			});
			
			snippet.elements = usedElements;
			snippet.markAttached();
		}
		
		static create(loadJob: LoadJob) {
			let lj = new LoadJobGroup(loadJob.url);
			lj.add(loadJob);
			return lj;
		}
	}
}