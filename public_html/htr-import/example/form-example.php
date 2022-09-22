<?php

function sanitizeDigit ($string) {
	return filter_var($string, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) ?: null;
}

function e($string) {
	echo $string;
}

$itemId = sanitizeDigit($_GET['itemId']);
$storyId = sanitizeDigit($_GET['storyId']);

?>

<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Transkribus Importer</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/light.css">

	<style>
		button {vertical-align: bottom; position: relative; bottom: 4px;}
		#loading { vertical-align: middle; margin: 13px 10px 0 0; display: inline-block; width: 20px; height: 20px; border: 3px solid rgba(255,255,255,.3); border-radius: 50%; border-top-color: #fff; animation: spin 1s ease-in-out infinite; -webkit-animation: spin 1s ease-in-out infinite; }
		@keyframes spin { to { -webkit-transform: rotate(360deg); } }
		@-webkit-keyframes spin { to { -webkit-transform: rotate(360deg); } }
        body {
			max-width: 1600px!important;
			background-color: #fff;
		}
		body a{
			color: #0a72cc;
		}
		.main-content {
			width: 80%;
			margin: 0 auto;
			display: block;
		}
		.main-h {
			width: 430px;
			margin: 50px auto;
		}
		.transkribus-nav {
			width: 100%;
			height: 60px;
		}
		.tr-form {
			text-align: center;
		}
		.logo {
			max-height: 60px;
			max-width: 200px;
		}
		.nav-item {
			position: relative;
			bottom: 40%;
		}
		.tr-models {
			width: 80%;
			margin: 50px auto;
		}
		.footer-logo {
			max-height: 80px;
			max-width: 200px;
		}
		.ft-logos {
			display: flex;
			flex-direction: row;
			justify-content: space-between;
		}
		.card {
			width: 18rem;
			border: 1px solid;
			margin: 10px auto;
			display: inline-block;
			height: 22rem;
		}
		.card:hover{
			width: 19rem;
			height: 23rem;
			box-shadow: 5px 5px #888888;
			margin: 0;
		}
		.card-img-top {
			height: 140px;
			width: inherit;
		}
		.card-body {
			padding: 0 10px 10px;
		}
		.card-body p{
			margin: 0;
		}
		.row {
			display: flex;
			flex-direction: row;
			justify-content: space-between;
		}
		.back-button{
			position: absolute;
			top: 344px;
			float: left;
			display: block;
			text-align: center;
			font-weight: 600;
			width: 18rem;
			margin: 25px auto;
			border: 3px solid;
			color: #000;
		}

	</style>
	<script src="//unpkg.com/alpinejs" defer></script>
</head>
<body>
    <!-- Added Navbar -->
	<nav class='transkribus-nav'>
	    <img class='logo' src="https://eu-citizen.science/media/images/2021-10-13_010110103636_734_transcribathon_eu_logo.jpg.612x408_q85_crop_upscale.png" alt='transcribathon-logo'/>
	    <img class='logo' style='float:right;' src="https://europeana.transcribathon.eu/wp-content/uploads/sites/11/2019/09/europeana-transcribe.png" alt='logo'/>

	</nav>

    <div class='main-content'>
	    <h1 class="main-h">Transkribus Importer</h1>

	    <div class="tr-form" x-data="htrForm">

	    	<h2>Import stories or items by HTR model</h2>

	    	<p>
					<?php if (!$itemId) { ?>
						<label>Story ID<input type="number" x-model.number="storyId" /></label> or
					<?php } ?>
					<?php if (!$storyId) { ?>
						<label>Item ID<input type="number" x-model.number="itemId" /></label>
					<?php } ?>
	    		<label>HTR Model ID
	    			<input id="id-input" type="text" x-model="htrId" list="htrList"/>
	    			<datalist id="htrList">
	    				<option></option>
	    				<template x-for="model in htrModels.trpModelMetadata" :key="model.modelId">
	    					<option :value="model.modelId" x-text="model.name"></option>
	    				</template>
	    			</datalist>
	    		</label>
	    		<span id="loading" x-show="processing"></span><button @click="getHtrData" x-bind:disabled="disabled" x-text="(processing || (!processing && percent !== 0)) ? percent.toFixed() + '% done': 'Import'"></button></p>
	    	<p x-show="(importResponse.amount > 0 || processing)" x-text="processText"></p>
	    	<p x-show="errors">Some errors occured, see console for output.</p>

	    </div>

		<hr>
		<!-- List of HTR Models -->
        <div class='tr-models'>
	        <h2>Test HTR model with Transcribathon item ID</h2>

		    <h3> Transkribus Public Models </h3>


			<!-- Checkbox Filter -->
			<div x-data="{types: ['print','handwritten']}">
			    <div>
                    <input id="print" type="checkbox" value="print" x-model="types">
			        <label for="print">Print/Typewritten</label>
			        <input id="hand" type="checkbox" value="handwritten" x-model="types">
			        <label for="hand">Handwritten</label>
			    </div>


                <!-- template wrapper to get conditional access to the 'cards' -->
				<!-- Handwritten models -->
			    <template x-if="types.includes('handwritten')">
		            <div class="row">

                        <div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2021/02/Kurrent_example__-1024x385.jpg" class="card-img-top" alt="example"/>
			            	<div class="card-body">
			            	    <h5 class="card-title">Transkribus German Kurrent</h5>
			            		<p class="card-text"><b>Id:</b> 29820</p>
			            		<p class="card-text"><b>By:</b> Transkribus Team</p>
			            		<p class="card-text"><b>Type:</b> Handwritten</p>
			        			<p class="card-text"><b>Language:</b> German</p>
			            		<a href="https://readcoop.eu/model/german-kurrent-and-sutterlin-17th-20th-century/" target="_blank">View more at Readcoop.eu</a>
			            	</div>
			            </div>

			            <div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2021/06/Polish-1024x567.jpg" class="card-img-top" alt="example-two"/>
			            	<div class="card-body">
			            	    <h5 class="card-title">Polish General Model</h5>
			            		<p class="card-text"><b>Id:</b> 33744</p>
			            		<p class="card-text"><b>By:</b> Transkribus Team</p>
			            		<p class="card-text"><b>Type:</b> Handwritten</p>
			        			<p class="card-text"><b>Language:</b> Polish</p>
			            		<a href="https://readcoop.eu/model/polish-general-model/" target="_blank">View more at Readcoop.eu</a>
			            	</div>
			            </div>

			    		<div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2020/07/word-image-268.png" class="card-img-top" alt="example-three"/>
			            	<div class="card-body">
			            	    <h5 class="card-title">Swedish 17th Century</h5>
			            		<p class="card-text"><b>Id:</b> 13685</p>
			            		<p class="card-text"><b>By:</b> Transkribus Team</p>
			            		<p class="card-text"><b>Type:</b> Handwritten</p>
			    	    		<p class="card-text"><b>Language:</b> Swedish</p>
			            		<a href="https://readcoop.eu/model/swedish-17th-century/" target="_blank">View more at Readcoop.eu</a>
			            	</div>
			            </div>

			    	</div>

				</template>
				<!-- Could probably put all the handwritten models into 1 template -->
				<template x-if="types.includes('handwritten')">
                    <!-- 'row' wrapper to hold 3 cards per row, responsivness is missing -->
					<div class="row">

			            <div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2021/06/French_example-1024x780.jpg" class="card-img-top" alt="example-four"/>
			            	<div class="card-body">
			            	    <h5 class="card-title">French General Model</h5>
			            		<p class="card-text"><b>Id:</b> 33597</p>
			            		<p class="card-text"><b>By:</b> Transkribus Team</p>
			            		<p class="card-text"><b>Type:</b> Handwritten</p>
				        		<p class="card-text"><b>Language:</b> French</p>
			            		<a href="https://readcoop.eu/model/french-general-model/" target="_blank">View more at Readcoop.eu</a>
			            	</div>
			            </div>

						<div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2021/03/Charter-Scripts.jpg" class="card-img-top" alt="example-five"/>
			        	    <div class="card-body">
			        	        <h5 class="card-title">Charter Scripts(German, Latin, French)</h5>
			        		    <p class="card-text"><b>Id:</b> 19872</p>
			        		    <p class="card-text"><b>By:</b> Tobias Hodel</p>
			        		    <p class="card-text"><b>Type:</b> Handwritten</p>
				    		    <p class="card-text"><b>Language:</b> German, Latin, French</p>
			        		    <a href="https://readcoop.eu/model/charter-scripts-german-latin-french/" target="_blank">View more at Readcoop.eu</a>
			        	    </div>
			            </div>

						<div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2020/07/word-image-267.png" class="card-img-top" alt="example-six"/>
			    	        <div class="card-body">
			    	            <h5 class="card-title">Latin & Neo-Latin</h5>
			    		        <p class="card-text"><b>Id:</b> 22408</p>
			    		        <p class="card-text"><b>By:</b> Several Contributors</p>
			    		        <p class="card-text"><b>Type:</b> Handwritten</p>
						        <p class="card-text"><b>Language:</b> Latin</p>
			    		        <a href="https://readcoop.eu/model/neo-latin/" target="_blank">View more at Readcoop.eu</a>
			    	        </div>
			            </div>

					</div>

			    </template>
				<!-- Print/Typewritten Models -->
			    <template x-if="types.includes('print')">

				    <div class="row">
		                <div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2021/04/Transkribus-Print-0.3-1.jpg" class="card-img-top" alt="example-seven"/>
			            	<div class="card-body">
			            	    <h5 class="card-title">Transkribus Print Multi-Language</h5>
			            		<p class="card-text"><b>Id:</b> 29418</p>
			            		<p class="card-text"><b>By:</b> Transkribus Team</p>
			            		<p class="card-text"><b>Type:</b> Print, Typewritten</p>
				        		<p class="card-text"><b>Language:</b> Multilanguage</p>
			            		<a href="https://readcoop.eu/model/print-multi-language-danish-dutch-german-finnish-french-latin-swedish/" target="_blank">View more at Readcoop.eu</a>
			            	</div>
			            </div>


			            <div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2021/03/Transkribus-Typewriter-Snippet.jpg" class="card-img-top" alt="example-eight"/>
			            	<div class="card-body">
			            	    <h5 class="card-title">Transkribus Typewriter</h5>
			            		<p class="card-text"><b>Id:</b> 33744</p>
			            		<p class="card-text"><b>By:</b> Transkribus Team</p>
			            		<p class="card-text"><b>Type:</b> Typewritten/Print</p>
				        		<p class="card-text"><b>Language:</b> Multilanguage</p>
			            		<a href="https://readcoop.eu/model/typewritten-english-german-dutch-finnish/" target="_blank">View more at Readcoop.eu</a>
			            	</div>
			            </div>


			            <div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2020/07/word-image-264.png" class="card-img-top" alt="example-nine"/>
			            	<div class="card-body">
			            	    <h5 class="card-title">German Fraktur 18th-20th Century</h5>
			            		<p class="card-text"><b>Id:</b> 12664</p>
			            		<p class="card-text"><b>By:</b> University of Zürich</p>
			            		<p class="card-text"><b>Type:</b> Typewritten/Print</p>
				        		<p class="card-text"><b>Language:</b> German</p>
			            		<a href="https://readcoop.eu/model/german-fraktur-18th-20th-century/" target="_blank">View more at Readcoop.eu</a>
			            	</div>
			            </div>

			        </div>

				</template>
				<template x-if="types.includes('print')">
                    <div class="row">

					    <div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2022/05/Prayer-Book-1538-1540-folio-12a.jpg" class="card-img-top" alt="example-ten"/>
			            	<div class="card-body">
			            	    <h5 class="card-title">Dionisio 1.0</h5>
			            		<p class="card-text"><b>Id:</b> 39359</p>
			            		<p class="card-text"><b>By:</b> Vladimir Polomac</p>
			            		<p class="card-text"><b>Type:</b> Typewritten/Print</p>
				        		<p class="card-text"><b>Language:</b> Serbian Cyrillic</p>
			            		<a href="https://readcoop.eu/model/dionisio-1-0/" target="_blank">View more at Readcoop.eu</a>
			            	</div>
			            </div>

						<div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2020/07/word-image-257.png" class="card-img-top" alt="example-eleven"/>
			            	<div class="card-body">
			            	    <h5 class="card-title">Devanagari Nagara 19th Century</h5>
			            		<p class="card-text"><b>Id:</b> 7884</p>
			            		<p class="card-text"><b>By:</b> Heidelberg University Library</p>
			            		<p class="card-text"><b>Type:</b> Typewritten/Print</p>
				        		<p class="card-text"><b>Language:</b> Devanagari</p>
			            		<a href="https://readcoop.eu/model/devanagari-nagara-19th-century/" target="_blank">View more at Readcoop.eu</a>
			            	</div>
			            </div>

						<div class="card">
			                <img src="https://readcoop.eu/wp-content/uploads/2021/03/Gothic_Annemieke.jpg" class="card-img-top" alt="example-twelwe"/>
			            	<div class="card-body">
			            	    <h5 class="card-title">Dutch Gothic Print 16th-18th Century</h5>
			            		<p class="card-text"><b>Id:</b> 18944</p>
			            		<p class="card-text"><b>By:</b> Entangled Histories</p>
			            		<p class="card-text"><b>Type:</b> Typewritten/Print</p>
				        		<p class="card-text"><b>Language:</b> Dutch</p>
			            		<a href="https://readcoop.eu/model/dutch-gothic-print-16th-18th-century/" target="_blank">View more at Readcoop.eu</a>
			            	</div>
			            </div>




					</div>
				</template>

            </div>
		</div>

		<!-- Back to Transcribathon Button -->
        <div style="width:80%;margin: 0 auto;">
		    <a class="back-button" href="https://europeana.transcribathon.local/documents/" style="color: #0a72cc;">Back To Transcribathon</a>
		</div>

    </div>
<script>

document.addEventListener('alpine:init', () => {

	Alpine.data('htrForm', () => ({

		loc: window.location,
		pathname: '/wp-content/themes/transcribathon/htr-client/request.php',
		htrModels: {},
		storyId: <?php e($storyId ?? 'null'); ?>,
		itemId: <?php e($itemId ?? 'null'); ?>,
		htrId: null,
		percent: 0,
		processing: false,
		disabled: false,
		errors: false,
		importResponse: {
			amount: 0,
			success: 0,
			errors: 0,
			error:  false
		},
		processText: 'Sending images to Transkribus...',

		async init () {

			const params = new URLSearchParams({
				htrModel: '1'
			});
			const url = this.loc.origin + this.pathname + '?' + params;

			this.htrModels = await (await fetch(url)).json();

			console.log(this.htrModels);

			if (!this.htrModels) {
				alert('Could not get data from HTR models API');
			}

		},

		async getHtrData () {

			if ((!this.storyId && !this.itemId) || !this.htrId) {

				return;

			}

			const params = new URLSearchParams({
				storyId: this.storyId,
				itemId: this.itemId,
				htrId: this.htrId
			});
			const url = this.loc.origin + this.pathname + '?' + params;

			this.disabled = true;
			this.processing = true;

			const query = async () => {

				this.importResponse = await (await fetch(url)).json();

				console.log(this.importResponse);

				if (this.importResponse.error) {
					alert('Could not get data from API');
					this.disabled = false;
					this.processing = false;
				}

				if (this.importResponse.amount > 0 && this.importResponse.success === 0 && this.importResponse.errors === 0) {
					this.percent = 1;
					this.processText = this.importResponse.amount + ' images are sent to Transkribus and initially stored in TP database, processing...';
				}

				if (this.importResponse.success > 0 || this.importResponse.errors > 0) {
					this.percent = (this.importResponse.success + this.importResponse.errors) / this.importResponse.amount * 100;
					this.processText = this.importResponse.success + '/'  + this.importResponse.amount + ' images are successfully transcribed. ' + this.importResponse.errors + ' images failed in transcribing.';
				}

				if (this.percent === 100) {
					this.disabled = true;
					this.processing = false;
					this.errors = this.importResponse.errors ? true : false;
				}

				if (this.processing) {
					setTimeout(() => {
						query.call()
					}, 5000);
				}

			};

			query();

		}

	}));

});
// fill model ID by clicking on card
var ready = (callback) => {
    if (document.readyState != "loading") callback();
    else document.addEventListener("DOMContentLoaded", callback);
}
// Replacement for jQuery document.ready; It runs the code after DOM is completely loaded
ready(() => {
const cards = document.querySelectorAll('.card');
console.log(cards);
for(let card of cards) {
	card.addEventListener('click', function() {
		let cardID = card.querySelector('.card-text').textContent;
		let cleanId = cardID.replace('Id: ', '');
		document.querySelector('#id-input').value = cleanId;
	})
}
})
</script>
</body>
</html>
