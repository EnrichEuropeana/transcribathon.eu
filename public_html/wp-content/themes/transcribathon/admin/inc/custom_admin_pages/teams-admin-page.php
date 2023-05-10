<?php

function _TCT_teams_admin_page($atts)
{
	$themeUri = get_stylesheet_directory_uri();
	$mainUri = get_europeana_url();
	$users = json_encode(
		get_users([
			'fields' => ['user_nicename', 'id'],
			// 'role'   => 'subscriber',
			'exclude' => [1]
		])
	);

	$tailwindLabelClasses = <<<TW1
		before:content[' ']
		after:content[' ']
		pointer-events-none
		absolute
		left-0
		-top-1.5
		flex
		h-full
		w-full
		select-none
		text-[11px]
		font-normal
		leading-tight
		text-blue-gray-400
		transition-all
		before:pointer-events-none
		before:mt-[6.5px]
		before:mr-1
		before:box-border
		before:block
		before:h-1.5
		before:w-2.5
		before:rounded-tl-md
		before:border-t
		before:border-l
		before:border-blue-gray-200
		before:transition-all
		after:pointer-events-none
		after:mt-[6.5px]
		after:ml-1
		after:box-border
		after:block
		after:h-1.5
		after:w-2.5
		after:flex-grow
		after:rounded-tr-md
		after:border-t
		after:border-r
		after:border-blue-gray-200
		after:transition-all
		peer-placeholder-shown:text-sm
		peer-placeholder-shown:leading-[3.75]
		peer-placeholder-shown:text-blue-gray-500
		peer-placeholder-shown:before:border-transparent
		peer-placeholder-shown:after:border-transparent
		peer-focus:text-[11px]
		peer-focus:leading-tight
		peer-focus:text-blue-500
		peer-focus:before:border-t-2
		peer-focus:before:border-l-2
		peer-focus:before:border-blue-500
		peer-focus:after:border-t-2
		peer-focus:after:border-r-2
		peer-focus:after:border-blue-500
		peer-disabled:text-transparent
		peer-disabled:before:border-transparent
		peer-disabled:after:border-transparent
		peer-disabled:peer-placeholder-shown:text-blue-gray-500
TW1;

	$tailwindInputClasses = <<<TW2
		peer
		h-full
		w-full
		rounded-[7px]
		border
		border-blue-gray-200
		border-t-transparent
		bg-transparent
		px-3
		py-2.5
		text-sm
		font-normal
		text-blue-gray-700
		outline
		outline-0
		transition-all
		placeholder-shown:border
		placeholder-shown:border-blue-gray-200
		placeholder-shown:border-t-blue-gray-200
		focus:border-2
		focus:border-blue-500
		focus:border-t-transparent
		focus:outline-0
		disabled:border-0
		disabled:bg-blue-gray-50
TW2;

	$tailwindInputWrapperClasses = <<<TW3
		relative
		mb-4
		h-11
		w-full
		min-w-[200px]
TW3;

	$html = <<<HTML
<div class="container mx-auto mt-8" x-data="manage_teams" id="team-mangement">
    <h1 class="text-2xl mb-4">Team Management</h1>

    <div class="bg-white p-6 rounded shadow-md">

			<div class="{$tailwindInputWrapperClasses}">
				<input
					class="{$tailwindInputClasses}"
					x-model="selectedTeam.Name"
					placeholder=" "
				/>
				<label
					class="{$tailwindLabelClasses}"
				>Team Name</label>
			</div>

			<div class="{$tailwindInputWrapperClasses}">
				<input
					class="{$tailwindInputClasses}"
					x-model="selectedTeam.ShortName"
					placeholder=" "
				/>
				<label
					class="{$tailwindLabelClasses}"
				>Team Short Name</label>
			</div>

			<div class="{$tailwindInputWrapperClasses}">
				<input
					class="{$tailwindInputClasses}"
					x-model="selectedTeam.Description"
					placeholder=" "
				/>
				<label
					class="{$tailwindLabelClasses}"
				>Team Description</label>
			</div>

			<div class="{$tailwindInputWrapperClasses}">
				<input
					class="{$tailwindInputClasses}"
					x-model="selectedTeam.Code"
					placeholder=" "
				/>
				<label
					class="{$tailwindLabelClasses}"
				>Team Code (only visible when inserting)</label>
			</div>

			<div class="{$tailwindInputWrapperClasses}">
				<input
					class="{$tailwindInputClasses}"
					x-model="searchTerm"
					@keyup="searchTeamMember()"
					placeholder=" "
				/>
				<label
					class="{$tailwindLabelClasses}"
				>Search and add Team Member</label>
			</div>

			<div class="mb-4 min-h-11">
				<ul class="
					flex
					flex-row
					flex-wrap
					content-start
					items-center
					gap-2
					mt-3
				">
					<template x-for="member in teamMemberSearch">
						<li class="">
							<button
								class="
									center
									relative
									inline-block
									select-none
									whitespace-nowrap
									rounded-lg
									bg-green-500
									hover:bg-green-600
									py-2
									px-3.5
									align-baseline
									text-xs
									font-bold
									leading-none
									text-white
								"
								x-text="member.user_nicename"
								@click="addTeamMember(member.id, member.user_nicename);"
							></button>
						</li>
					</template>
				</ul>
			</div>
      <div class="mb-4">
        <div class="
					{$tailwindInputClasses}
					min-h-[5rem]
					w-full
					flex
					flex-row
					flex-wrap
					content-start
					items-center
					gap-1
				">
					<template x-for="user in selectedTeam.Users">
						<div class="
							center
							relative
							inline-block
							select-none
							whitespace-nowrap
							rounded-lg
							bg-teal-500
							py-2
							px-3.5
							align-baseline
							text-xs
							font-bold
							leading-none
							text-white
						">
							<div class="mr-5 mt-px" x-text="user.nicename"></div>
							<div class="
								absolute
								top-1
								right-1
								mx-px
								mt-[0.5px]
								w-max
								rounded-md
								bg-red-500
								transition-colors
								hover:bg-red-600
							">
								<div
									role="button"
									class="h-5 w-5 p-1"
									@click="removeTeamMember(user.UserId)"
								>
      						<svg
        						xmlns="http://www.w3.org/2000/svg"
        						fill="none"
        						viewBox="0 0 24 24"
        						stroke="currentColor"
        						stroke-width="3"
      						>
        						<path
          						stroke-linecap="round"
          						stroke-linejoin="round"
          						d="M6 18L18 6M6 6l12 12"
        						></path>
      						</svg>
    						</div>
  						</div>
						</div>
					</template>
				</div>
      </div>

      <div class="flex justify-end">
      	<button
					type="submit"
					class="
						mr-3
						rounded-lg
						bg-blue-500
						py-3
						px-6
						text-xs
						font-bold
						uppercase
						text-white
						shadow-md
						shadow-blue-500/20
						transition-all
						hover:shadow-lg
						hover:shadow-blue-500/40
						active:opacity-[0.85]
						active:shadow-none
					"
					@click="saveTeam(selectedTeam.TeamId)"
				>Save</button>
        <button
					type="button"
					class="
						rounded-lg
						border
						border-blue-500
						py-3
						px-6
						text-xs
						font-bold
						uppercase
						text-blue-500
						transition-all
						hover:opacity-75
						active:opacity-[0.85]
					"
					@click="resetForm"
				>Cancel</button>
      </div>
    </div>

    <table class="
			w-full
			mt-4
			text-sm
			text-left
			text-gray-500
		">
  		<thead class="
				text-xs
				text-gray-700
				uppercase
				bg-gray-50
			">
        <tr>
            <th scope="col" class="px-6 py-3">Team Name</th>
            <th scope="col" class="px-6 py-3">Description</th>
            <th scope="col" class="px-6 py-3">Actions</th>
        </tr>
        </thead>
        <tbody>
					<template x-for="(team, index) in teams" :key="team.TeamId">
						<tr
							class="border-b"
							:class="index % 2 === 0 ? 'bg-whight' : 'bg-gray-50'"
						>
							<th cope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap" x-text="team.Name"></td>
							<td class="px-6 py-4" x-text="team.Description"></td>
							<td class="flex px-6 py-4">
								<button
									class="
										mr-3
										rounded-lg
										bg-blue-500
										py-2
										px-4
										text-xs
										font-bold
										uppercase
										text-white
										shadow-md
										shadow-blue-500/20
										transition-all
										hover:shadow-lg
										hover:shadow-blue-500/40
										active:opacity-[0.85]
										active:shadow-none
									"
                  @click="loadTeam(team.TeamId)"
								>Edit</button>
                <button
									class="
										mr-3
										rounded-lg
										bg-red-500
										py-2
										px-4
										text-xs
										font-bold
										uppercase
										text-white
										shadow-md
										shadow-red-500/20
										transition-all
										hover:shadow-lg
										hover:shadow-red-500/40
										active:opacity-[0.85]
										active:shadow-none
									"
                  @click="removeTeam(team.TeamId)"
                >Delete</button>
							</td>
						</tr>
					</template>
					</tbody>
    </table>
  	<button
			type="button"
			@click="closeToast()"
			x-show="toast"
			x-transition.duration.300ms
			class="
				fixed top-16 right-4 z-50 rounded-lg p-4 text-base leading-5 font-bold text-white opacity-100 transition
				"
			:class="toastType === 'success' ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600'"
		>
      <p x-text="toastMessage"></p>
		</button>
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

function load_teams_scripts($hook)
{
	if ($hook !== 'toplevel_page_teams-admin-page') {
		return;
	}
	// using the playground tailwindcss for now, no compiling but heavier load
	// wp_register_script('add_tailwindcss', get_stylesheet_directory_uri(). '/admin/inc/custom_js/tailwindcss.min.js', [], '3.3.1', false);
	// wp_enqueue_script('add_tailwindcss');
	wp_register_style('add_css', get_stylesheet_directory_uri(). '/admin/inc/custom_admin_pages/teams-admin-page-css.min.css', [], '3.3.2', false);
	wp_register_script('add_alpinejs', get_stylesheet_directory_uri(). '/admin/inc/custom_js/alpinejs.min.js', [], '3.12.0', false);
	wp_register_script('add_team_script', get_stylesheet_directory_uri(). '/admin/inc/custom_admin_pages/teams-admin-page.js', [], '0.1.0', true);
	wp_enqueue_style('add_css');
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
