document.addEventListener('alpine:init', () => {

	Alpine.data('manage_campaigns', () => ({

		filterString: '',
		toast: false,
		toastType: 'success',
		toastMessage: '',
		campaigns: [],
		teams: [],
		selectedCampaign: {},
		searchTerm: '',
		teamSearch: [],
		datasets: [],
		scrollElement: document.querySelector('#campaign-mangement'),

		async init() {

			this.resetForm();

			const campaignData = await (await fetch(THEME_URI + '/api-request.php/campaigns?limit=500&page=1&orderBy=End&orderDir=desc')).json();

			if (!campaignData.success) {

				this.openToast('error', campaignData.error);
				return;

			}

			this.campaigns = campaignData.data;

			const datasetData = await (await fetch(THEME_URI + '/api-request.php/datasets?limit=500')).json();

			if (!datasetData.success) {

				this.openToast('error', datasetData.error);
				return;

			}

			this.datasets = datasetData.data;

		},

		async addTeam (teamId, teamName) {

			const team = {
				TeamId: teamId,
				Name: teamName
			}

			if (this.selectedCampaign.Teams.find(selectedTeam => selectedTeam.TeamId === team.TeamId)) {

				this.searchTerm = '';
				this.teamSearch = [];
				return;

			}

			this.selectedCampaign.Teams.push(team);

			this.searchTerm = '';
			this.teamSearch = [];

		},

		async searchTeam () {

			if (this.searchTerm.length < 3) {

				return;

			}

			const teamsData = await (await fetch(THEME_URI + '/api-request.php/teams?limit=500')).json();

			if (!teamsData.success) {

				this.openToast('error', 'Could not reach teams endpoint.')
				return;

			}

			this.teams = teamsData.data;

			this.teamSearch = this.teams.filter(
				team => team.Name.toLowerCase().includes(this.searchTerm.toLowerCase())
			);

		},

		loadCampaign(campaignId) {

			this.selectedCampaign = this.campaigns.find(c => c.CampaignId === campaignId);
			this.scrollElement.scrollIntoView({ behavior: 'smooth' });

    },

    removeTeam(teamId) {

			this.selectedCampaign.Teams = this.selectedCampaign.Teams.filter(team => team.TeamId !== teamId);

    },

    async removeCampaign(campaignId) {

			const remove = await (await fetch(THEME_URI + '/api-request.php/campaigns/' + campaignId, {
				method: 'DELETE'
			})).json();

			if (!remove.success) {

				this.openToast('error', 'Could not delete campaign.');
				return;

			}

			this.campaigns = this.campaigns.filter(campaign => campaign.CampaignId !== campaignId);
			this.openToast('success', 'Campaign deleted.');

    },

    saveCampaign(campaignId = null) {

			if (this.selectedCampaign.Name === '') {

				return;

			}

			if (!campaignId) {

				this.createCampaign();
				return;

			}

			this.updateCampaign(campaignId);

    },

		async updateCampaign(campaignId) {

			const teamIdArray = [];
			for (const team of this.selectedCampaign.Teams) {

				teamIdArray.push(team.TeamId);

			}

			const payload = {
				Name: this.selectedCampaign.Name,
				Start: this.selectedCampaign.Start,
				End: this.selectedCampaign.End,
				Public: this.selectedCampaign.Public,
				DatasetId: this.selectedCampaign.DatasetId,
				TeamIds: [...teamIdArray]
			};

			const updated = await (await fetch(THEME_URI + '/api-request.php/campaigns/' + campaignId, {
				method: 'PUT',
				body: JSON.stringify(payload)
			})).json();

			if (updated.success) {

				this.campaigns.map(campaign => campaign.CampaignId === campaignId ? updated.data : campaign);
				this.resetForm();
				this.openToast('success', 'Campaign updated.');

			} else {

				this.openToast('error', 'Campaign could not updated.');

			}

		},

		async createCampaign() {

			const teamIdArray = [];
			for (const team of this.selectedCampaign.Teams) {

				teamIdArray.push(team.TeamId);

			}

			const payload = {
				Name: this.selectedCampaign.Name,
				Start: this.selectedCampaign.Start,
				End: this.selectedCampaign.End,
				Public: this.selectedCampaign.Public === '1' ? true : false,
				DatasetId: this.selectedCampaign.DatasetId,
				TeamIds: [...teamIdArray]
			};

			const created = await (await fetch(THEME_URI + '/api-request.php/campaigns', {
				method: 'POST',
				body: JSON.stringify(payload)
			})).json();

			if (created.success) {

				this.selectedCampaign = { ...created.data };
				this.campaigns.push(this.selectedCampaign);
				this.resetForm();
				this.openToast('success', 'Campaign created.');

			} else {

				this.openToast('error', 'Campaign could not created.');

			}

		},

    resetForm() {

    	this.selectedCampaign = {
    		CampaignId: '',
				Name: '',
				Start: '',
				End: '',
				DatasetId: '',
				Public: '0',
				Teams: []
    	};

    },

		filterTable () {

			if (!this.filterString) {

				return;

			}

			const elRows = document.querySelectorAll('tr[data-action="filter"]');
			const filterStrings = this.filterString.split(' ');

			elRows.forEach(row => {

				const rowText = row.innerText.toLowerCase();
				let check = true;
				row.style.display = 'none';

				filterStrings.forEach(string => {

					check = (rowText.search(string.toLowerCase()) !== -1 && check === true) ? true : false;

				});

				if (check === true) {

					row.style.display = 'table-row';

				}

			});

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
