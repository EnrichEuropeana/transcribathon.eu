document.addEventListener('alpine:init', () => {

	Alpine.data('manage_teams', () => ({

		toast: false,
		toastType: 'success',
		toastMessage: '',
		teams: [],
		wpUsers: JSON.parse(ALL_USERS),
		selectedTeam: {},
		searchTerm: '',
		teamMemberSearch: [],
		scrollElement: document.querySelector('#team-mangement'),

		async init() {

			this.resetForm();

			const teamData = await (await fetch(THEME_URI + '/api-request.php/teams?limit=500&page=1&orderBy=TeamId&orderDir=asc')).json();

			if (!teamData.success) {

				this.openToast('error', teamData.error);
				return;

			}

			this.teams = teamData.data;

		},

		async addTeamMember (wpUserId, nicename) {

			const userData = await (await fetch(THEME_URI + '/api-request.php/users?limit=1&page=1&orderBy=UserId&orderDir=asc&WP_UserId=' + wpUserId)).json();

			if (!userData.success) {

				this.openToast('error', 'Could not add user to team.')
				return;

			}

			const user = {
				UserId: userData.data[0].UserId,
				WP_UserId: wpUserId,
				nicename: nicename
			}

			if (this.selectedTeam.Users.find(selectedUser => selectedUser.UserId === user.UserId)) {

				this.searchTerm = '';
				this.teamMemberSearch = [];
				return;

			}

			this.selectedTeam.Users.push(user);

			this.searchTerm = '';
			this.teamMemberSearch = [];

		},

		searchTeamMember () {

			if (this.searchTerm.length < 3) {

				return;

			}

			this.teamMemberSearch = this.wpUsers.filter(
				wpUser => wpUser.user_nicename.toLowerCase().includes(this.searchTerm.toLowerCase())
			);

		},

		loadTeam(teamId) {

			this.selectedTeam = this.teams.find(t => t.TeamId === teamId);
			this.selectedTeam.Users.forEach((user, index, array) => {
				array[index].nicename = this.wpUsers.find(wpUser => parseInt(wpUser.id) === parseInt(user.WP_UserId))?.user_nicename || user.WP_UserId;
			});
			this.scrollElement.scrollIntoView({ behavior: 'smooth' });

    },

    removeTeamMember(userId) {

			this.selectedTeam.Users = this.selectedTeam.Users.filter(user => user.UserId !== userId);

    },

    async removeTeam(teamId) {

			const remove = await (await fetch(THEME_URI + '/api-request.php/teams/' + teamId, {
				method: 'DELETE'
			})).json();

			if (!remove.success) {

				this.openToast('error', 'Could not remove team.');
				return;

			}

			this.teams = this.teams.filter(team => team.TeamId !== teamId);
			this.openToast('success', 'Team removed.');

    },

    saveTeam(teamId = null) {

			if (this.selectedTeam.Name === '') {

				return;

			}

			if (!teamId) {

				this.createTeam();
				return;

			}

			this.updateTeam(teamId);

    },

		async updateTeam(teamId) {

			const userIdArray = [];
			for (const user of this.selectedTeam.Users) {

				userIdArray.push(user.UserId);

			}

			const payload = {
				Name: this.selectedTeam.Name,
				ShortName: this.selectedTeam.ShortName,
				Description: this.selectedTeam.Description,
				UserIds: [...userIdArray]
			};

			if (this.selectedTeam.Code !== '') {

				payload.Code = this.selectedTeam.Code;

			}

			const updated = await (await fetch(THEME_URI + '/api-request.php/teams/' + teamId, {
				method: 'PUT',
				body: JSON.stringify(payload)
			})).json();

			if (updated.success) {

				this.teams.map(team => team.TeamId === teamId ? updated.data : team);
				this.resetForm();
				this.openToast('success', 'Team updated.');

			} else {

				this.openToast('error', 'Team could not updated.');

			}

		},

		async createTeam() {

			const userIdArray = [];
			for (const user of this.selectedTeam.Users) {

				userIdArray.push(user.UserId);

			}

			const payload = {
				Name: this.selectedTeam.Name,
				ShortName: this.selectedTeam.ShortName,
				Description: this.selectedTeam.Description,
				UserIds: [...userIdArray],
				Code: this.selectedTeam.Code
			};

			const created = await (await fetch(THEME_URI + '/api-request.php/teams', {
				method: 'POST',
				body: JSON.stringify(payload)
			})).json();

			if (created.success) {

				this.selectedTeam = { ...created.data };
				this.teams.push(this.selectedTeam);
				this.resetForm();
				this.openToast('success', 'Team created.');

			} else {

				this.openToast('error', 'Team could not created.');

			}

		},

    resetForm() {

    	this.selectedTeam = {
				Description: '',
				Name: '',
				ShortName: '',
				TeamId: null,
				Code: '',
				Users: []
    	};

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
