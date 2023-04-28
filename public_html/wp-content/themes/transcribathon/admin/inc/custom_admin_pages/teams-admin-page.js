document.addEventListener('alpine:init', () => {

	Alpine.data('manage_teams', () => ({

		teams: [],
		wpUsers: JSON.parse(ALL_USERS),
		selectedTeam: {},
		scrollElement: document.querySelector('#team-mangement'),

		async init() {

			this.resetForm();

			const teamData = await (await fetch(THEME_URI + '/api-request.php/teams?limit=500&page=1&orderBy=TeamId&orderDir=asc')).json();

			if (!teamData.success) {

				alert(teamData.error);
				return;

			}

			this.teams = teamData.data;

		},

		editTeam(teamId) {

			this.selectedTeam = this.teams.find(t => t.TeamId === teamId);
			this.selectedTeam.Users.forEach((user, index, array) => {
				array[index].nicename = this.wpUsers.find(wpUser => parseInt(wpUser.id) === parseInt(user.WP_UserId))?.user_nicename || user.WP_UserId;
			});
			this.scrollElement.scrollIntoView({ behavior: 'smooth' });

    },

    saveTeam(teamId) {

			console.log(teamId);

    },

    resetForm() {

    	this.selectedTeam = {
				Description: '',
				Name: '',
				ShortName: '',
				TeamId: null,
				Users: []
    	};

    }

	}));

});

function addTeam() {
    const teamName = document.getElementById('teamName').value;
    const teamMembers = document.getElementById('teamMembers').value;

    if (teamName === '') {
        alert('Team name is required.');
        return;
    }

    teams.push({
        id: Date.now(),
        name: teamName,
        members: teamMembers
    });

    resetForm();
    updateTeamList();
}

// Edit team

// Delete team
function deleteTeam(teamId) {
    teams = teams.filter(t => t.id !== teamId);
    updateTeamList();
}

