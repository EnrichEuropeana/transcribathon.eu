<?php

/**
 * Shortcode: htr_import
 * Description: page with the forms for the HTR imports
*/

// include required files
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

function customCSS ()
{
	$css = <<<EOF

html {
	font-size: 100%;
}

body {
	max-width: inherit;
	margin: inherit;
	padding: inherit;
}

button {
	background-image: inherit;
	height: fit-content;
	padding: inherit;
	position: relative;
	bottom: -25px;
}

button:focus {
	border: 0px solid transparent;
	background: #007bff;
}

.row {
	display: flex;
	flex-direction: row;
	justify-content: flex-start;
	gap: 1rem;
}

.card {
	position: relative;
	flex: 0 1 32%;
	cursor: pointer;
}

.card:hover {
	border-color: #007bff;
	top: -2px;
	left: -2px;
}

.card-title {
	font-size: 1rem !important;
	font-weight: bold !important;
}

.card-text {
	display: block;
	font-size: 0.9rem !important;
}

input {
	padding: 0.375rem !important;
}

#loading {
	vertical-align: middle;
	margin: 0 0.5rem 0 0;
	display: inline-block;
	width: 20px;
	height: 20px;
	border: 3px solid rgba(0,123,255,.3);
	border-radius: 50%;
	border-top-color: #007bff;
	animation: spin 1s ease-in-out infinite;
	-webkit-animation: spin 1s ease-in-out infinite;
}

@keyframes spin { to { -webkit-transform: rotate(360deg); } }
@-webkit-keyframes spin { to { -webkit-transform: rotate(360deg); } }

EOF;

	return $css;
}

function sanitizeDigit ($string)
{
	return filter_var(
		$string,
		FILTER_VALIDATE_INT,
		array('options' => array('min_range' => 1))
	) ?: null;
}

function getInputs($ids = null, $type = null)
{
	$input = array();
	$input[] = $type !== 'stories' ? '<label class="col">Item ID<input class="form-control" type="text" x-model="itemId" /></label>' : '';
	$input[] = $type === null ? '<span>or</span>' : '';
	$input[] = $type !== 'items' ? '<label class="col">Story ID<input class="form-control" type="text" x-model="storyId" /></label>' : '';

	$out = implode("\n", $input);

	return $out;
}

function _TCT_htr_import()
{
  $isLoggedIn = is_user_logged_in();
	$itemId = sanitizeDigit($_GET['itemId']);
	$storyId = sanitizeDigit($_GET['storyId']);

	$alpineJs = get_stylesheet_directory_uri(). '/js/alpinejs.3.10.4.min.js';

	$requestUri = get_stylesheet_directory_uri() . '/htr-client/request.php';

	$solrImportWrapperUri = get_stylesheet_directory_uri() . '/solr-import-request.php';

	$labels = $itemId
		? getInputs($itemId, 'items')
		: ($storyId ? getInputs($storyId, 'stories') : getInputs());

	$customCSS = customCSS();

	$safeItemId = $itemId ?: 'null';
	$safeStoryId = $storyId ?: 'null';

	$backlink = get_europeana_url() . '/documents/';
	$backtext = 'Back to the documents';

	if ($itemId) {
		$backlink = get_europeana_url() . '/documents/story/item/?item=' . $itemId;
		$backtext = 'Back to the item';
	}

	if ($storyId) {
		$backlink = get_europeana_url() . '/documents/story/?story=' . $storyId;
		$backtext = 'Back to the story';
	}

    if (!$isLoggedIn) {
        echo '<div id="default-login-container" style="display:block;">';
		    echo '<div id="default-login-popup">';
		    	echo '<div class="default-login-popup-header theme-color-background">';
		    		echo '<span class="item-login-close">&times;</span>';
		    	echo '</div>';
		    	echo '<div class="default-login-popup-body">';
		    		$login_post = get_posts( array(
		    			'name'    => 'default-login',
		    			'post_type'    => 'um_form',
		    		));
		    		echo do_shortcode('[ultimatemember form_id="'.$login_post[0]->ID.'"]');
		    	echo '</div>';
		    	echo '<div class="default-login-popup-footer theme-color-background">';
		    	echo '</div>';
		    echo '</div>';
	    echo '</div>';

    } else {

	$out = <<<OUT

<style>{$customCSS}</style>

<div x-data="htrForm">

	<div class="back-to-story">
		<a href="{$backlink}"><i class="fas fa-arrow-left"></i> {$backtext}</a>
	</div>

	<h2>Import stories or items by HTR model</h2>

	<p class="row">
		{$labels}
	  <label class="col">Document Language
			<select id="languageId" class="form-control" x-model="languageId">
	    	<option></option>
	    	<template x-for="language in languages.data" :key="language.LanguageId">
	    		<option :value="language.LanguageId" x-text="language.NameEnglish"></option>
	    	</template>
	    </select>
	  </label>
	  <label class="col">HTR Model ID
	    <input class="form-control" id="id-input" type="text" x-model="htrModelId" list="htrList"/>
	    <datalist id="htrList">
	    	<option></option>
	    	<template x-for="model in htrModels.trpModelMetadata" :key="model.modelId">
	    		<option :value="model.modelId" x-text="model.name"></option>
	    	</template>
	    </datalist>
	  </label>
		<button
			class="btn btn-primary"
			@click="getHtrData"
			x-bind:disabled="disabled"
			x-text="(processing || (!processing && percent !== 0)) ? percent.toFixed() + '% done': 'Import'"
		></button>
	</p>
	<p class="alert my-4" :class="('alert-' + status)" role="alert">
		<span id="loading" x-show="processing"></span>
		</span>
		<span x-text="processText"></span>
	</p>
	<p class="alert alert-info my-4" x-show="showStatus">
		Initialized: <span x-text="itemStatus.initialized.length"></span>/<span x-text="itemStatus.amount"></span>
		<br />
		Transcribed: <span x-text="itemStatus.transcribed.length"></span>/<span x-text="itemStatus.amount"></span>
		<br />
		Errors: <span x-text="itemStatus.errors.length"></span>/<span x-text="itemStatus.amount"></span>
	</p>

	<h2>HTR public models</h2>

	<p>
		<label class="w-100">Filter model:
			<input
				class="form-control"
				type="text"
				x-model="filterString"
				@keyup="filterModels"
				placeholder="handwritten swe 18th"
			/>
		</label>
	</p>

	<div class="container">
		<div class="row" id="htrModels">
			<template x-for="model in htrModels.trpModelMetadata" :key="model.modelId">
				<div class="card">
					<div class="card-body" @click="htrModelId = model.modelId">
						<h5 class="card-title" x-text="model.name"></h5>
						<span class="card-text"><b>Languages:</b> <span x-text="Array.isArray(model.isoLanguages) ? model.isoLanguages.join(', ') : 'n.n.'"></span></span>
						<span class="card-text"><b>Material:</b> <span x-text="model.docType"></span></span>
						<span class="card-text"><b>Script:</b> <span x-text="Array.isArray(model.scriptTypes) ? model.scriptTypes.join(', ') : 'n.n.'"></span></span>
						<span class="card-text"><b>Centuries:</b> <span x-text="Array.isArray(model.centuries) ? model.centuries.join('th, ') + 'th' : 'n.n.'"></span></span>
						<span class="card-text"><b>Creator:</b> <span x-text="model.creator"></span></span>
						<span class="card-text"><b>HTR Model ID:</b> <span x-text="model.modelId"></span></span>
					</div>
				</div>
			</template>
		</div>
	</div>

</div>

<script>

document.addEventListener('alpine:init', () => {

	Alpine.data('htrForm', () => ({

		solrImportWrapper: '{$solrImportWrapperUri}',
		solrApiCommand: '/solr/Items/dataimport?command=delta-import&commit=true',
		filterString: '',
		requestUri: '{$requestUri}',
		htrModels: {},
		languages: {},
		storyId: {$safeStoryId},
		itemId: {$safeItemId},
		itemIds : [],
		languageId: null,
		htrModelId: null,
		percent: 0,
		processing: true,
		showStatus: false,
		disabled: false,
		status: 'warning', // info, danger, warning, success
		itemStatus: {
			amount: 0,
			initialized: [],
			transcribed: [],
			errors: [],
			error: false
		},
		processText: 'Loading all HTR models, please wait...',

		async init () {

			const params = new URLSearchParams({
				htrModel: '1'
			});
			const url = this.requestUri + '?' + params;

			this.htrModels = await (await fetch(url)).json();

			console.log(this.htrModels);

			if (!this.htrModels) {
				this.processText = 'Could not get data from HTR models API.';
				this.processing = false;
				this.status = 'danger';
			} else {
				this.processText = 'HTR models API loaded. You can begin to import.';
				this.processing = false;
				this.status = 'success';
			}

			const languageParams = new URLSearchParams({
				languages: '1'
			});
			const langUrl = this.requestUri + '?' + languageParams;

			this.languages = await (await fetch(langUrl)).json();

			this.languages.data = this.languages.data.sort((a, b) => {
				if (a.NameEnglish < b.NameEnglish) {
					return -1;
				}
			});

		},

		async getItemIds () {

			const itemIds = [];

			if (this.itemId) {
				itemIds.push(this.itemId);
				return itemIds;
			}

			const params = new URLSearchParams({
				storyId: this.storyId,
			});
			const url = this.requestUri + '?' + params;

			const data = await (await fetch(url)).json();

			if (data['error']) {
				this.processing = false;
				return [];
			}

			return data['data']['ItemIds'];

		},

		async processImage (item) {

			if (!item) {
				return;
			}

			// item already succeded
			if (this.itemStatus.transcribed.indexOf(item) >= 0) {
				return;
			}

			const errorIndex       = this.itemStatus.errors.indexOf(item);
			const initializedIndex = this.itemStatus.initialized.indexOf(item);

			const params = new URLSearchParams({
				itemId: item,
				htrModelId: this.htrModelId,
				languageId: this.languageId
			});

			const url = this.requestUri + '?' + params;

			const data = await (await fetch(url)).json();

			this.percent = (this.itemStatus.initialized.length + this.itemStatus.transcribed.length + this.itemStatus.errors.length) * 100 / (this.itemStatus.amount * 2);

			if (data.errors) {
				if (errorIndex < 0) {
					this.itemStatus.errors.push(item);
				}
				this.itemStatus.error = data.error;
				return data;
			}

			if (data.success) {
				this.itemStatus.transcribed.push(item);

				// if not already in initialized index from previous passes insert it
				if (initializedIndex < 0) {
					this.itemStatus.initialized.push(item);
				}

				// if item succeeds in one of the following passes remove it from errors
				if (errorIndex >= 0) {
					this.itemStatus.errors.splice(errorIndex, 1);
				}

				return data;
			}

			if (initializedIndex < 0) {
				this.itemStatus.initialized.push(item);
			}

			return data;
		},

		async updateSolr () {

			const solrUpdate =  await (await fetch(this.solrImportWrapper + this.solrApiCommand)).json();

		},

		async getHtrData () {

			this.showStatus = false;

			if ((!this.storyId && !this.itemId) || !this.htrModelId || !this.languageId) {

				this.processText = 'Not all input fields are filled.';
				this.status = 'danger';
				return;

			}

			if (this.itemIds.length <= 0) {
				this.itemIds = await this.getItemIds();
			}

			this.itemStatus.amount = this.itemIds.length;

			if (this.itemStatus.amount < 1) {
				this.processText = 'Could not get item data from API.';
				this.status = 'danger';
				this.disabled = false;
				this.processing = false;
				this.showStatus = false;
				return;
			}

			this.processText = 'Processing ' + this.itemStatus.amount + ' images...please wait.';
			this.status = 'warning';
			this.disabled = true;
			this.processing = true;
			this.showStatus = true;

			const query = async () => {

				for (let i = 0 ; i < this.itemIds.length; i++) {

					const result = await this.processImage(this.itemIds[i]);

				}

				if (this.itemStatus.errors.length + this.itemStatus.transcribed.length >= this.itemStatus.amount) {

					if (this.itemStatus.transcribed.length === this.itemStatus.amount) {
						this.processText = 'All ' + this.itemStatus.amount + ' images successfully transcribed.';
						this.status = 'success';
						this.disabled = false;
						this.processing = false;
						this.showStatus = true;
						this.updateSolr();
						return;
					}

					if (this.itemStatus.error.length >= this.itemStatus.amount) {
						this.processText = 'None of the ' + this.itemStatus.amount + ' images could be transcribed.';
						this.status = 'error';
						this.disabled = false;
						this.processing = false;
						this.showStatus = true;
						return;
					}

					this.processText = 'Some of the ' + this.itemStatus.amount + ' images could be transcribed.';
					this.status = 'warning';
					this.disabled = false;
					this.processing = false;
					this.showStatus = true;
					this.updateSolr();
					return;
				}

				if (this.processing) {
					setTimeout(
						() => {
							console.log('Running pass!');
							query();
						},
						5000
					);
				}

			}

			query();

		},


		filterModels () {

			if (!this.filterString) {

				return;

			}

			const elCards = document.querySelectorAll('#htrModels .card');
			const filterStrings = this.filterString.split(' ');

			elCards.forEach(card => {

				const cardText = card.innerText.toLowerCase();
				let check = true;
				card.style.display = 'none';

				filterStrings.forEach(string => {

					check = (cardText.search(string.toLowerCase()) !== -1 && check === true) ? true : false;

				});

				if (check === true) {

					card.style.display = 'block';

				}

			});

		}

	}));

});

// When the user clicks the button(pen on the image viewer), open the login modal
jQuery('#default-lock-login').click(function() {
	jQuery('#default-login-container').css('display', 'block');
})
// When the user clicks on <span> (x), close the modal
jQuery('.item-login-close').click(function() {
	jQuery('#default-login-container').css('display', 'none');
	if(jQuery('.site-navigation').hasClass("fullscreen")){
		jQuery("nav").removeClass("fullscreen");
		jQuery(".site-navigation").css('display', 'none');
	}
})

</script>
<script src="{$alpineJs}"></script>
OUT;
	}

	return $out;
}

add_shortcode('htr_import', '_TCT_htr_import');
