<?php

function _TCT_teams_admin_page($atts)
{
	$themeUri = get_stylesheet_directory_uri();
	$mainUri = get_europeana_url();
	$users = json_encode(
		get_users([
			'fields' => ['user_nicename', 'id'],
			'role'   => 'subscriber',
			'exclude' => [1]
		])
	);

	$html = <<<HTML
<div class="container mx-auto mt-8" x-data="manage_teams">
    <h1 class="text-2xl mb-4">Team Management</h1>
    <div id="teamForm" class="bg-white p-6 rounded shadow-md">
        <input type="hidden" id="teamId">
        <div class="mb-4">
            <label for="teamName" class="block mb-2">Team Name</label>
            <input type="text" id="teamName" class="w-full px-4 py-2 border border-gray-300 rounded" placeholder="Enter team name">
        </div>
        <div class="mb-4">
            <label for="teamMembers" class="block mb-2">Team Members</label>
            <textarea id="teamMembers" class="w-full px-4 py-2 border border-gray-300 rounded" placeholder="Enter team members"></textarea>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">Save</button>
            <button type="button" id="cancelBtn" class="ml-2 px-4 py-2 bg-gray-500 text-white rounded">Cancel</button>
        </div>
    </div,>
    <table id="teamTable" class="mt-8 w-full table-auto bg-white rounded shadow-md">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2">Team Name</th>
                <th class="px-4 py-2">Description</th>
                <th class="px-4 py-2 w-40">Actions</th>
            </tr>
        </thead>
        <tbody>
					<template x-for="team in teams" :key="team.TeamId">
						<tr>
							<td class="px-4 py-2 border-y-2 border-gray-200" x-text="team.Name"></td>
							<td class="px-4 py-2 border-y-2 border-gray-200" x-text="team.Description"></td>
							<td class="px-4 py-2 border-y-2 border-gray-200">
								<button
                    class="px-2 py-1 bg-blue-500 text-white rounded"
                    @click="editTeam(team.TeamId)"
                >Edit</button>
                <button
                    class="ml-2 px-2 py-1 bg-red-500 text-white rounded"
                    @click="deleteTeam(team.id)"
                >Delete</button>
							</td>
						</tr>
					</template>
					</tbody>
    </table>
</div>
HTML;

$js = <<<JS
<script>
	const THEME_URI = '{$themeUri}';
	const MAIN_URI = '{$mainUri}';
	const ALL_USERS = '{$users}';
</script>
JS;

	echo $html . $js;
}

function teams_menu()
{
	add_menu_page(
		'Teams',
		'Teams',
		'manage_options',
		'teams-admin-page',
		'_TCT_teams_admin_page',
		'dashicons-groups',
		3
  );
}

function load_teams_scripts()
{
	// using the playground tailwindcss for now, no compiling but heavier load
	wp_register_script('add_tailwindcss', get_stylesheet_directory_uri(). '/admin/inc/custom_js/tailwindcss.min.js', [], '3.3.1', false);
	wp_register_script('add_alpinejs', get_stylesheet_directory_uri(). '/admin/inc/custom_js/alpinejs.min.js', [], '3.12.0', false);
	wp_register_script('add_team_script', get_stylesheet_directory_uri(). '/admin/inc/custom_admin_pages/teams-admin-page.js', [], '0.1.0', true);
	wp_enqueue_script('add_tailwindcss');
	wp_enqueue_script('add_alpinejs');
	wp_enqueue_script('add_team_script');
	add_filter('script_loader_tag', 'defer_alpinejs', 10, 3);
}

function defer_alpinejs($tag, $handle, $src)
{
	if ('add_alpinejs' === $handle) {
		$tag = '<script defer src="' . esc_url($src) . '"></script>';
	}
	return $tag;
}


add_action('admin_enqueue_scripts', 'load_teams_scripts');
add_action('admin_menu', 'teams_menu');
