<?php

/**
 * Shortcode: htr_import
 * Description: page with the forms for the HTR imports
*/

// include required files
include($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

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
	$itemIds = sanitizeDigit($_GET['itemId']);
	$storyIds = sanitizeDigit($_GET['storyId']);

	$alpineJs = get_stylesheet_directory_uri(). '/js/alpinejs.3.10.4.min.js';

	$requestUri = get_stylesheet_directory_uri() . '/htr-client/request.php';

	$labels = $itemIds
		? getInputs($itemIds, 'items')
		: ($storyIds ? getInputs($storyIds, 'stories') : getInputs());

	$customCSS = customCSS();

	$safeItemIds = $itemIds ?: 'null';
	$safeStorIds = $storyIds ?: 'null';

	$backlink = get_europeana_url() . '/documents/';
	$backtext = 'Back to the documents';

	if ($itemIds) {
		$backlink = get_europeana_url() . '/documents/story/item/?item=' . $itemIds;
		$backtext = 'Back to the item';
	}

	if ($storyIds) {
		$backlink = get_europeana_url() . '/documents/story/?story=' . $storyIds;
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
	  <label class="col">HTR Model ID
	    <input class="form-control" id="id-input" type="text" x-model="htrId" list="htrList"/>
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
		<span x-text="processText"></span>
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
					<div class="card-body" @click="htrId = model.modelId">
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

		filterString: '',
		requestUri: '{$requestUri}',
		htrModels: {},
		storyId: {$safeStorIds},
		itemId: {$safeItemIds},
		htrId: null,
		percent: 0,
		processing: true,
		disabled: false,
		status: 'warning', // info, danger, warning, success
		importResponse: {
			amount: 0,
			success: 0,
			errors: 0,
			error:  false
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

		},

		async getHtrData () {

			if ((!this.storyId && !this.itemId) || !this.htrId) {

				this.processText = 'Not all input fields are filled.';
				this.status = 'danger';
				return;

			}

			const params = new URLSearchParams({
				storyId: this.storyId,
				itemId: this.itemId,
				htrId: this.htrId
			});
			const url = this.requestUri + '?' + params;

			this.processText = 'Processing...please wait.';
			this.status = 'warning';
			this.disabled = true;
			this.processing = true;

			const query = async () => {

				this.importResponse = await (await fetch(url)).json();

				console.log(this.importResponse);

				if (this.importResponse.error) {
					this.processText = 'Could not get story or item data from API.';
					this.status = 'danger';
					this.disabled = false;
					this.processing = false;
				}

				if (this.importResponse.amount > 0
						&& this.importResponse.success === 0
						&& this.importResponse.errors === 0) {
					this.percent = 1;
					this.processText = this.importResponse.amount + ' images are sent to Transkribus and initially stored in TP database, processing...';
					this.status = 'info';
				}

				if (this.importResponse.success > 0 || this.importResponse.errors > 0) {
					this.percent = (this.importResponse.success + this.importResponse.errors) / this.importResponse.amount * 100;
					this.processText = this.importResponse.success + '/'  + this.importResponse.amount + ' images are successfully transcribed. ' + this.importResponse.errors + ' images failed in transcribing.';
					this.status = 'warning';
				}

				if (this.percent === 100) {
					this.disabled = false;
					this.processing = false;
					this.status = this.importResponse.errors
						? (this.importResponse.errors < this.importResponse.amount
								? 'warning'
								: 'danger')
						: 'success';
				}

				const intervall = this.storyId ? 180000 : 5000;

				if (this.processing) {
					setTimeout(() => {
						query.call()
					}, intervall);
				}

			};

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
