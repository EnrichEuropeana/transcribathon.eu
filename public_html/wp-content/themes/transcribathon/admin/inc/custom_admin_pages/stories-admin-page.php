<?php

function _TCT_stories_admin_page($atts)
{
	$themeUri = get_stylesheet_directory_uri();
	$mainUri = get_europeana_url();
	$solrImportWrapper = $themeUri . '/solr-import-request.php';

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
		max-w-none
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
		focus:shadow-none
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
<div class="container mx-auto mt-8" x-data="manage_stories" id="story-mangement">
	<h1 class="text-2xl mb-4">Story Management</h1>

<!--

	<div class="bg-white p-6 rounded shadow-md">

		<div class="{$tailwindInputWrapperClasses}">
			<input
				class="{$tailwindInputClasses}"
				x-model="selectedCampaign.Name"
				placeholder=" "
			/>
			<label
				class="{$tailwindLabelClasses}"
			>Campaign Name</label>
		</div>

		<div class="{$tailwindInputWrapperClasses}">
			<input
				type="datetime-local"
				class="{$tailwindInputClasses}"
				x-model="selectedCampaign.Start"
				placeholder=" "
			/>
			<label
				class="{$tailwindLabelClasses}"
			>Campaign Start</label>
		</div>

		<div class="{$tailwindInputWrapperClasses}">
			<input
				type="datetime-local"
				class="{$tailwindInputClasses}"
				x-model="selectedCampaign.End"
				placeholder=" "
			/>
			<label
				class="{$tailwindLabelClasses}"
			>Campaign End</label>
		</div>

		<div class="{$tailwindInputWrapperClasses}">
			<select
				class="{$tailwindInputClasses}"
				x-model="selectedCampaign.DatasetId"
			>
				<option></option>
				<template x-for="dataset in datasets">
					<option
						:value="dataset.DatasetId"
						x-text="dataset.Name"
						:selected="selectedCampaign.DatasetId === dataset.DatasetId"
					></option>
				</template>
			</select>
			<label
				class="{$tailwindLabelClasses}"
			>Associated Dataset</label>
		</div>

		<div class="
			{$tailwindInputWrapperClasses}
			inline-flex
			items-center
		">
		<div
			class="
				relative
				inline-block
				h-4
				w-8
				ml-2
				cursor-pointer
				rounded-full
			"
		>
			<input
				id="auto-update"
				type="checkbox"
				x-model="selectedCampaign.Public"
				placeholder=" "
				class="
					peer
					absolute
					h-4
					w-8
					m-0
					cursor-pointer
					appearance-none
					rounded-full
					bg-gray-200
					border-none
					focus:border-none
					transition-colors
					duration-300
					checked:bg-blue-500
					peer-checked:border-blue-500
					peer-checked:before:bg-blue-500
					checked:before:content['']
					checked:before:m-0
				"
			/>
			<label
				for="auto-update"
				class="
					before:content['']
					absolute
					top-2/4
					-left-1
					h-5
					w-5
					-translate-y-2/4
					cursor-pointer
					rounded-full
					border
					border-blue-gray-100
					bg-white
					shadow-md
					transition-all
					duration-300
					before:absolute
					before:top-2/4
					before:left-2/4
					before:block
					before:h-10
					before:w-10
					before:-translate-y-2/4
					before:-translate-x-2/4
					before:rounded-full
					before:bg-blue-gray-500
					before:opacity-0
					before:transition-opacity
					hover:before:opacity-10
					peer-checked:translate-x-full
					peer-checked:border-blue-500
					peer-checked:before:bg-blue-500
				"
			></label>
		</div>
			<label
				for="auto-update"
				class="
					mt-px
					ml-3
					mb-0
					cursor-pointer
					select-none
				"
			>Public</label>
		</div>

		<div class="{$tailwindInputWrapperClasses}">
			<input
				class="{$tailwindInputClasses}"
				x-model="searchTerm"
				@keyup="searchTeam()"
				placeholder=" "
			/>
			<label
				class="{$tailwindLabelClasses}"
			>Search and add Teams</label>
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
				<template x-for="team in teamSearch">
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
							x-text="team.Name"
							@click="addTeam(team.TeamId, team.Name);"
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
				<template x-for="team in selectedCampaign.Teams">
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
						<div class="mr-5 mt-px" x-text="team.Name"></div>
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
								@click="removeTeam(team.TeamId)"
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
				@click="saveCampaign(selectedCampaign.CampaignId)"
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

-->

	<div class="mt-4 bg-white p-6 rounded shadow-md">

		<div class="flex {$tailwindInputWrapperClasses}">
			<input
				x-model="searchString"
				class="{$tailwindInputClasses}"
				placeholder=" "
				@keydown.enter="search"
			/>
			<label
				class="{$tailwindLabelClasses}"
			>Search stories by title here (otherwise latest 100 imported stories are shown)</label>
			<button
				class="
					ml-3
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
				@click="search"
				@keydown.enter="search"
			>Search</button>
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
					<th scope="col" class="px-6 py-3">StoryId</th>
					<th scope="col" class="px-6 py-3">Title</th>
					<th scope="col" class="px-6 py-3 min-w-[9rem]">Dataset</th>
					<th scope="col" class="px-6 py-3">Actions</th>
			</tr>
			</thead>
			<tbody>
				<template x-for="(story, index) in stories" :key="story.StoryId">
					<tr
						data-action="filter"
						class="border-b"
						:class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
					>
						<th cope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap" x-text="story.StoryId"></td>
						<td class="px-6 py-4" x-text="story.Dc?.Title || story.dcTitle"></td>
						<td class="px-6 py-4" x-text="datasets.find(set => set.DatasetId === story.DatasetId)?.Name || story.Dataset || ''"></td>
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
								@click="loadStory(story.StoryId)"
							>Edit</button>
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
	const solrWrapper = '{$solrImportWrapper}';
</script>
JS;

	echo $html . $js;
}

function load_stories_scripts($hook)
{
	if ($hook !== 'toplevel_page_stories-admin-page') {
		return;
	}
	// using the playground tailwindcss for now, no compiling but heavier load
	// wp_register_script('add_tailwindcss', get_stylesheet_directory_uri(). '/admin/inc/custom_js/tailwindcss.min.js', [], '3.3.1', false);
	// wp_enqueue_script('add_tailwindcss');
	wp_register_style('add_css', get_stylesheet_directory_uri(). '/admin/inc/custom_admin_pages/backend.min.css', [], '3.3.2', false);
	wp_register_script('add_alpinejs', get_stylesheet_directory_uri(). '/admin/inc/custom_js/alpinejs.min.js', [], '3.12.0', false);
	wp_register_script('add_stories_script', get_stylesheet_directory_uri(). '/admin/inc/custom_admin_pages/stories-admin-page.js', [], '0.1.0', true);
	wp_enqueue_style('add_css');
	wp_enqueue_script('add_alpinejs');
	wp_enqueue_script('add_stories_script');
	add_filter('script_loader_tag', 'defer_alpinejs', 10, 3);
}

function defer_alpinejs($tag, $handle, $src)
{
	if ('add_alpinejs' === $handle) {
		$tag = '<script defer src="' . esc_url($src) . '"></script>';
	}
	return $tag;
}

add_action('admin_enqueue_scripts', 'load_stories_scripts');
