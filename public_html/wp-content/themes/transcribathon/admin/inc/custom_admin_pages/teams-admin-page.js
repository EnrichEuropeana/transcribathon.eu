document.addEventListener('alpine:init', () => {

	Alpine.data('manage_teams', () => ({

		teams: [],
		users: JSON.parse(ALL_USERS),

		async init() {

			const teamData = await (await fetch(THEME_URI + '/api-request.php/teams?limit=500&page=1&orderBy=TeamId&orderDir=asc')).json();

			console.log(this.users);
			if (!teamData.success) {

				alert(userData.error);
				return;

			}

			this.teams= teamData.data;

		}

	}));

});

// Teams array to store team data
let teams = [];

// Add new team
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
function editTeam(teamId) {
    const team = teams.find(t => t.id === teamId);
    if (team) {
        document.getElementById('teamName').value = team.name;
        document.getElementById('teamMembers').value = team.members;
        document.getElementById('editTeamId').value = teamId;
    }
}

// Delete team
function deleteTeam(teamId) {
    teams = teams.filter(t => t.id !== teamId);
    updateTeamList();
}

// Reset form
function resetForm() {
    document.getElementById('teamName').value = '';
    document.getElementById('teamMembers').value = '';
    document.getElementById('editTeamId').value = '';
}

// Update team list
function updateTeamList() {
    const teamList = document.getElementById('teamList');
    teamList.innerHTML = '';

    teams.forEach(team => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-4 py-2">${team.name}</td>
            <td class="px-4 py-2">${team.members}</td>
            <td class="px-4 py-2">
                <button
                    class="px-2 py-1 bg-blue-500 text-white rounded-md"
                    onclick="editTeam(${team.id})"
                >Edit</button>
                <button
                    class="ml-2 px-2 py-1 bg-red-500 text-white rounded-md"
                    onclick="deleteTeam(${team.id})"
                >Delete</button>
            </td>
        `;
        teamList.appendChild(tr);
    });
}

