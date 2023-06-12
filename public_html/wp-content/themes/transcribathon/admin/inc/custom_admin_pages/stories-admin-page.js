document.addEventListener('alpine:init', () => {

	Alpine.data('manage_stories', () => ({

		searchString: '',
		toast: false,
		toastType: 'success',
		toastMessage: '',
		selectedStory: {},
		selectedStoryCampaigns: [],
		stories: [],
		initalStories: [],
		datasets: [],
		campaigns: [],
		searchedCampaigns: [],
		searchCampaignTerm: '',
		scrollElement: document.querySelector('#story-mangement'),

		async init() {

			this.resetForm();

			const storiesData = await (await fetch(THEME_URI + '/api-request.php/stories?limit=100&page=1&orderBy=StoryId&orderDir=desc')).json();

			if (!storiesData.success) {

				this.openToast('error', storiesData.error);
				return;

			}

			this.stories = this.initalStories = storiesData.data;

			const datasetData = await (await fetch(THEME_URI + '/api-request.php/datasets?limit=500')).json();

			if (!datasetData.success) {

				this.openToast('error', datasetData.error);
				return;

			}

			this.datasets = datasetData.data;

			const campaignData = await (await fetch(THEME_URI + '/api-request.php/campaigns?limit=500')).json();

			if (!campaignData.success) {

				this.openToast('error', campaignData.error);
				return;

			}

			this.campaigns = campaignData.data;

		},

		async loadStory(storyId) {

			this.selectedStory = this.stories.find(c => c.StoryId === storyId);
			this.scrollElement.scrollIntoView({ behavior: 'smooth' });

			const campaingsData = await (await fetch(THEME_URI + '/api-request.php/stories/' + storyId + '/campaigns')).json();

			this.selectedStoryCampaigns = [...campaingsData?.data];

    },

    async saveStory(storyId = null) {

			if (!storyId) {

				this.openToast('error', 'Nothing to save.');
				return;

			}

			const campaignsPayload = {};
			campaignsPayload.Campaigns = this.selectedStoryCampaigns.map(campaign => campaign.CampaignId);

			const campaignsUpdated = await (await fetch(THEME_URI + '/api-request.php/stories/' + storyId + '/campaigns', {
				method: 'PUT',
				body: JSON.stringify(campaignsPayload)
			})).json();

			const datasetPayload = {};
			datasetPayload.DatasetId = this.selectedStory.DatasetId;

			const datasetUpdated = await (await fetch(THEME_URI + '/api-request.php/stories/' + storyId, {
				method: 'PUT',
				body: JSON.stringify(datasetPayload)
			})).json();

			if (campaignsUpdated.success && datasetUpdated.success) {

				this.openToast('success', 'Story updated.');

			} else {

				this.openToast('error', 'Story could not updated.');

			}

    },

    searchCampaign() {

			if (this.searchCampaignTerm.length < 3) {

				this.searchedCampaigns = [];
				return;

			}

			this.searchedCampaigns = this.campaigns.filter(
				campaign => campaign.Name.toLowerCase().includes(this.searchCampaignTerm.toLowerCase())
			);


    },

		addCampaign (CampaignId, CampaignName) {


			this.selectedStoryCampaigns.push({
				CampaignId: CampaignId,
				Name: CampaignName
			});

			this.searchCampaignTerm = '';
			this.searchedCampaigns= [];

		},


    resetForm() {

    	this.selectedStory = {};
			this.selectedStoryCampaigns = [];
			this.searchedCampaigns = [];

    },

    removeCampaign(CampaignId) {

			this.selectedStoryCampaigns = this.selectedStoryCampaigns.filter(campaign => campaign.CampaignId !== CampaignId);

    },

    async search() {

			if (this.searchString.length < 3 && this.searchString.length > 0) {

				this.openToast('error', 'At least 3 chars are needed for search.');

				return;

			}

			if (this.searchString.length === 0) {

				this.stories = [...this.initalStories];

				return;

			}

			this.stories = [];

			const solrApiCommand = '/solr/Stories/select?rows=100&dcTitle=&q=*' + encodeURI(this.searchString) + '*';

			const solrResponse = await (await fetch(solrWrapper + solrApiCommand)).json();

			this.stories = solrResponse.response ? [...solrResponse.response.docs] : [];

    },

    openToast(type, message) {

    	if (this.toast) {

    		return;

    	}

    	this.toast = true;
    	this.toastType = type;
    	this.toastMessage = message;

			if (typeof timer !== 'undefined') {

				clearTimeout(timer);

			}

			timer = setTimeout(() => {

				this.toast = false;
				this.toastMessage = '';

      }, 3000);

    },

    closeToast() {

    	this.toast = false;
    	this.toastMessage = '';

    },

	}));

});
