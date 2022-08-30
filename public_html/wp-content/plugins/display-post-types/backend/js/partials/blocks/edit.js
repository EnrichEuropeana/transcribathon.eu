const { __ } = wp.i18n;
const { Component, Fragment, createRef } = wp.element;
const { InspectorControls } = wp.editor;
const { apiFetch } = wp;
const { PanelBody, TextControl, SelectControl, RangeControl, ToggleControl, ServerSideRender, VisuallyHidden, Card, CardBody, CardHeader } = wp.components;

import MultipleCheckboxControl from './mcc';

class DisplayPostTypes extends Component {

	constructor() {
		super( ...arguments );
		this.state = {
			postTypes: [],
			pageList: [],
			taxonomies: [],
			termsList: [],
			styleList: [],
		};
		this.fetching = false;
		this.styleSupport = {};
		this.elemRef = createRef();
	}

	apiDataFetch(data, path) {
		if (this.fetching) {
			setTimeout( this.apiDataFetch.bind(this, data, path), 200 );
			return;
		}
		let obj = {};
		this.fetching = true;
		apiFetch( {
			path: '/dpt/v1/' + path,
		} )
		.then( ( items ) => {
			let itemsList = Object.keys(items);
			itemsList = itemsList.map(item => {
				return {
					label: items[item],
					value: item,
				};
			});
			obj[data] = itemsList;
			this.setState(obj);
			this.fetching = false;
		} )
		.catch( () => {
			obj[data] = [];
			this.setState(obj);
			this.fetching = false;
		} );
	}
	
	componentDidMount() {
		const {attributes} = this.props;
		const {postType} = attributes;
		this.apiDataFetch('postTypes', 'posttypes');
		if (postType) {
			if ('page' === postType) {
				this.getPagesList();
			} else {
				this.updateTaxonomy();
				this.updateTerms();
			}
		}
		this.getStyleList();
	}

	componentDidUpdate( prevProps ) {
		const {
			postType: oldPostType,
			taxonomy: oldTaxonomy
		} = prevProps.attributes;
		const { postType, taxonomy } = this.props.attributes;

		if (oldPostType !== postType) {
			this.updateTaxonomy();
			if ('page' === postType) { this.getPagesList() }
		}

		if (oldTaxonomy !== taxonomy) { this.updateTerms() }
	}

	updateTaxonomy() {
		const { attributes } = this.props;
		const { postType } = attributes;
		if (!postType || 'page' === postType) {
			this.setState( { taxonomies: [], termsList: [] } );
		} else {
			this.apiDataFetch('taxonomies', 'taxonomies/' + postType);
		}
	}

	updateTerms() {
		const { attributes } = this.props;
		const { taxonomy } = attributes;
		if (!taxonomy) {
			this.setState( { termsList: [] } );
		} else {
			this.apiDataFetch('termsList', 'terms/' + taxonomy);
		}
	}

	getPagesList() {
		this.apiDataFetch('pageList', 'pagelist');
	}

	getStyleList() {
		apiFetch( {
			path: '/dpt/v1/stylelist',
		} )
		.then( ( items ) => {
			const list = Object.keys(items);
			const styleList = list.map(item => {
				return {
					label: items[item]['label'],
					value: item,
				};
			});
			list.forEach(item => {
				this.styleSupport[item] = items[item]['support'];
			});
			this.setState( { styleList } );
		} )
		.catch( () => {
			this.setState( { styleList: [] } );
		} );
	}

	render() {
		const { postTypes, taxonomies, pageList, termsList, styleList } = this.state;
		const { attributes, setAttributes } = this.props;
		const {
			postType,
			taxonomy,
			terms,
			relation,
			postIds,
			pages,
			number,
			orderBy,
			order,
			styles,
			styleSup,
			imageCrop,
			imgAspect,
			imgAlign,
			brRadius,
			colNarr,
			plHolder,
			showPgnation,
			textAlign,
			vGutter,
			hGutter,
			eLength,
			eTeaser,
			offset,
			autoTime,
			meta1,
			meta2,
		} = attributes;
		const onChangePostType = value => {
			setAttributes({ terms: [] });
			setAttributes({ taxonomy: '' });
			setAttributes({ postType: value });
		};
		const onChangeTaxonomy = value => {
			setAttributes({ terms: [] });
			setAttributes({ taxonomy: value });
		};
		const styleSupported = (style) => {
			const all = [
				{ value: 'thumbnail', label: __( 'Thumbnail', 'display-post-types' ) },
				{ value: 'title', label: __( 'Title', 'display-post-types' ) },
				{ value: 'meta', label: __( 'Meta Info 1', 'display-post-types' ) },
				{ value: 'category', label: __( 'Meta info 2', 'display-post-types' ) },
				{ value: 'excerpt', label: __( 'Excerpt', 'display-post-types' ) },
				{ value: 'date', label: __( 'Date', 'display-post-types' ) },
				{ value: 'ago', label: __( 'Ago', 'display-post-types' ) },
				{ value: 'author', label: __( 'Author', 'display-post-types' ) },
				{ value: 'content', label: __( 'Content', 'display-post-types' ) },
			];
			const supported = this.styleSupport[style];
			if ( 'undefined' === typeof supported ) return false;
			return all.filter(sup => {
				if ( 'category' !== sup.value ) {
					return supported.includes(sup.value);
				} else {
					return ( supported.includes(sup.value) && 'post' === postType );
				}
			});
		};
		const ifStyleSupport = (style, item) => {
			const supported = this.styleSupport[style];
			if ( 'undefined' === typeof supported ) return false;
			return supported.includes(item);
		}
		const termCheckChange = (value) => {
			const index = terms.indexOf(value);
			if (-1 === index) {
				setAttributes({ terms: [...terms, value] });
			} else {
				setAttributes({ terms: terms.filter(term => term !== value) });
			}
		};
		const pageCheckChange = (value) => {
			const index = pages.indexOf(value);
			if (-1 === index) {
				setAttributes({ pages: [...pages, value] });
			} else {
				setAttributes({ pages: pages.filter(page => page !== value) });
			}
		};
		const supCheckChange = (value) => {
			const index = styleSup.indexOf(value);
			if (-1 === index) {
				setAttributes({ styleSup: [...styleSup, value] });
			} else {
				setAttributes({ styleSup: styleSup.filter(sup => sup !== value) });
			}
		};
		const orderbyOptions = [
			{ value: 'date', label: __( 'Publish Date', 'display-post-types' ) },
			{ value: 'modified', label: __( 'Modified Date', 'display-post-types' ) },
			{ value: 'title', label: __( 'Title', 'display-post-types' ) },
			{ value: 'author', label: __( 'Author', 'display-post-types' ) },
			{ value: 'comment_count', label: __( 'Comment Count', 'display-post-types' ) },
			{ value: 'rand', label: __( 'Random', 'display-post-types' ) },
		];
		const aspectOptions = [
			{ value: '', label: __( 'No Cropping', 'display-post-types' ) },
			{ value: 'land1', label: __( 'Landscape (4:3)', 'display-post-types' ) },
			{ value: 'land2', label: __( 'Landscape (3:2)', 'display-post-types' ) },
			{ value: 'port1', label: __( 'Portrait (3:4)', 'display-post-types' ) },
			{ value: 'port2', label: __( 'Portrait (2:3)', 'display-post-types' ) },
			{ value: 'wdscrn', label: __( 'Widescreen (16:9)', 'display-post-types' ) },
			{ value: 'squr', label: __( 'Square (1:1)', 'display-post-types' ) },
		];
		const cropOptions = [
			{ value: 'topleftcrop', label: __( 'Top Left Cropping', 'display-post-types' ) },
			{ value: 'topcentercrop', label: __( 'Top Center Cropping', 'display-post-types' ) },
			{ value: 'centercrop', label: __( 'Center Cropping', 'display-post-types' ) },
			{ value: 'bottomcentercrop', label: __( 'Bottom Center Cropping', 'display-post-types' ) },
			{ value: 'bottomleftcrop', label: __( 'Bottom Left Cropping', 'display-post-types' ) },
		];

		const getElement = (iElem) => {
			switch(iElem.value) {
				case 'thumbnail':
					return getThumbnail();
				case 'title':
					return getTitle();
				case 'excerpt':
					return getExcerpt();
				case 'content':
					return getContent();
				case 'meta':
					return getMeta1();
				case 'category':
					return getMeta2();
				default:
					return '';
			}
		};

		const getThumbnail = () => {
			return (
				<div>
					<ToggleControl
						label={ __( 'Show Thumbnail', 'display-post-types' ) }
						checked={ !! styleSup.includes('thumbnail') }
						onChange={ () => { supCheckChange('thumbnail') } }
					/>
					<ToggleControl
						label={ __( 'Thumbnail Placeholder', 'display-post-types' ) }
						checked={ !! plHolder }
						onChange={ ( plHolder ) => setAttributes( { plHolder } ) }
					/>
					<SelectControl
						label={ __( 'Image Cropping', 'display-post-types' ) }
						value={ imgAspect }
						onChange={ ( imgAspect ) => setAttributes( { imgAspect } ) }
						options={ aspectOptions }
					/>
					{
						'' !== imgAspect &&
						<SelectControl
							label={ __( 'Image Cropping Position', 'display-post-types' ) }
							value={ imageCrop }
							onChange={ ( imageCrop ) => setAttributes( { imageCrop } ) }
							options={ cropOptions }
						/>
					}
					{
						(styles && ifStyleSupport(styles, 'ialign')) &&
						<SelectControl
							label={ __( 'Image Alignment', 'display-post-types' ) }
							value={ imgAlign }
							onChange={ ( imgAlign ) => setAttributes( { imgAlign } ) }
							options={ [
								{ value: '', label: __( 'Left Aligned', 'display-post-types' ) },
								{ value: 'right', label: __( 'Right Aligned', 'display-post-types' ) },
							] }
						/>
					}
				</div>
			);
		};
		
		const getTitle = () => {
			return (
				<div>
					<ToggleControl
						label={ __( 'Show Title', 'display-post-types' ) }
						checked={ !! styleSup.includes('title') }
						onChange={ () => { supCheckChange('title') } }
					/>
				</div>
			);
		};
		
		const getExcerpt = () => {
			return (
				<div>
					<ToggleControl
						label={ __( 'Show Excerpt', 'display-post-types' ) }
						checked={ !! styleSup.includes('excerpt') }
						onChange={ () => { supCheckChange('excerpt') } }
					/>
					{
						(styleSup.includes('excerpt') && ifStyleSupport(styles, 'excerpt')) &&
						<RangeControl
							label={ __( 'Excerpt Length (in words)', 'display-post-types' ) }
							value={ eLength }
							onChange={ ( eLength ) => setAttributes( { eLength } ) }
							min={ 0 }
						/>
					}
					{
						(styleSup.includes('excerpt') && ifStyleSupport(styles, 'excerpt')) &&
						<TextControl
							label={ __( 'Excerpt Teaser Text', 'display-post-types' ) }
							value={ eTeaser }
							onChange={ ( eTeaser ) => setAttributes( { eTeaser } ) }
							help={ __( 'i.e., Continue Reading, Read More', 'display-post-types' ) }
						/>
					}
				</div>
			);
		};
		
		const getContent = () => {
			return (
				<div>
					<ToggleControl
						label={ __( 'Show Full Content', 'display-post-types' ) }
						checked={ !! styleSup.includes('content') }
						onChange={ () => { supCheckChange('content') } }
					/>
				</div>
			);
		};
		
		const getMeta1 = () => {
			return (
				<div>
					<ToggleControl
						label={ __( 'Show Meta Info - 1', 'display-post-types' ) }
						checked={ !! styleSup.includes('meta') }
						onChange={ () => { supCheckChange('meta') } }
					/>
					<TextControl
						label={ __( 'Meta Info to be displayed', 'display-post-types' ) }
						value={ meta1 }
						onChange={ ( meta1 ) => setAttributes( { meta1 } ) }
					/>
				</div>
			);
		};
		
		const getMeta2 = () => {
			return (
				<div>
					<ToggleControl
						label={ __( 'Show Meta Info - 2', 'display-post-types' ) }
						checked={ !! styleSup.includes('category') }
						onChange={ () => { supCheckChange('category') } }
					/>
					<TextControl
						label={ __( 'Meta Info to be displayed', 'display-post-types' ) }
						value={ meta2 }
						onChange={ ( meta2 ) => setAttributes( { meta2 } ) }
					/>
				</div>
			);
		};

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Setup Display Post Types', 'display-post-types' ) }>
						{
							postTypes &&
							<SelectControl
								label={ __( 'Select a Post Type', 'display-post-types' ) }
								value={ postType }
								options={ postTypes }
								onChange={ (value) => onChangePostType(value) }
							/>
						}
					</PanelBody>
					<PanelBody initialOpen={ false } title={ __( 'Get items to be displayed', 'display-post-types' ) }>
						<div style={{paddingBottom: "10px"}}>
							<Card size="small">
								<CardHeader>
									{
										<label style={{fontWeight: "bold"}}>{ __( 'Filter Items', 'display-post-types' ) }</label>
									}
								</CardHeader>
								<CardBody>
									{
										!! taxonomies.length &&
										<SelectControl
											label={ __( 'Filter items by Taxonomy', 'display-post-types' ) }
											value={ taxonomy }
											options={ taxonomies }
											onChange={ ( value ) => onChangeTaxonomy(value) }
										/>
									}
									{
										('page' === postType && !! pageList.length) &&
										<MultipleCheckboxControl
											listItems={ pageList }
											selected={ pages }
											onItemChange={ pageCheckChange }
											label = { __( 'Select Pages', 'display-post-types' ) }
										/>
									}
									{
										!! termsList.length &&
										<MultipleCheckboxControl
											listItems={ termsList }
											selected={ terms }
											onItemChange={ termCheckChange }
											label = { __( 'Select Taxonomy Terms', 'display-post-types' ) }
										/>
									}
									{
										!! termsList.length &&
										<SelectControl
											label={ __( 'Terms Relationship', 'display-post-types' ) }
											value={ relation }
											onChange={ ( relation ) => setAttributes( { relation } ) }
											options={ [
												{ value: 'IN', label: __( 'OR - Show posts from any of the terms selected above.', 'display-post-types' ) },
												{ value: 'AND', label: __( 'AND - Show posts only if they belong to all of the selected terms.', 'display-post-types' ) },
											] }
										/>
									}
									{
										'page' !== postType &&
										<TextControl
											label={ __( 'Filter items by Post IDs (optional)', 'display-post-types' ) }
											value={ postIds }
											onChange={ ( postIds ) => setAttributes( { postIds } ) }
											help={ __( 'Comma separated ids, i.e. 230,300', 'display-post-types' ) }
										/>
									}
								</CardBody>
							</Card>
						</div>
						{
							'page' !== postType &&
							<div style={{paddingBottom: "10px"}}>
								<Card size="small">
									<CardHeader>
										{
											<label style={{fontWeight: "bold"}}>{ __( 'Sort Items', 'display-post-types' ) }</label>
										}
									</CardHeader>
									<CardBody>
										<SelectControl
											label={ __( 'Sort By', 'display-post-types' ) }
											value={ orderBy }
											onChange={ ( orderBy ) => setAttributes( { orderBy } ) }
											options={ orderbyOptions }
										/>
										<SelectControl
											label={ __( 'Sort Order', 'display-post-types' ) }
											value={ order }
											onChange={ ( order ) => setAttributes( { order } ) }
											options={ [
												{ value: 'DESC', label: __( 'Descending', 'display-post-types' ) },
												{ value: 'ASC', label: __( 'Ascending', 'display-post-types' ) },
											] }
										/>
									</CardBody>
								</Card>
							</div>
						}
						<div style={{paddingBottom: "10px"}}>
							<Card size="small">
								<CardHeader>
									{
										<label style={{fontWeight: "bold"}}>{ __( 'Display Settings', 'display-post-types' ) }</label>
									}
								</CardHeader>
								<CardBody>
									<RangeControl
										label={ __( 'Number of items to display', 'display-post-types' ) }
										value={ number }
										onChange={ ( number ) => setAttributes( { number } ) }
										min={ 1 }
									/>
									{
										'page' !== postType &&
										<RangeControl
											label={ __( 'Offset (number of posts to displace)', 'display-post-types' ) }
											value={ offset }
											onChange={ ( offset ) => setAttributes( { offset } ) }
											min={ 0 }
										/>
									}
									{
										(styles && !ifStyleSupport(styles, 'slider')) &&
										<ToggleControl
											label={ __( 'Show Pagination.', 'display-post-types' ) }
											checked={ !! showPgnation }
											onChange={ ( showPgnation ) => setAttributes( { showPgnation } ) }
										/>
									}
								</CardBody>
							</Card>
						</div>
					</PanelBody>
					<PanelBody initialOpen={ false } title={ __( 'Setup Items Layout', 'display-post-types' ) }>
						{
							!! styleList.length &&
							<SelectControl
								label={ __( 'Display Style', 'display-post-types' ) }
								value={ styles }
								onChange={ ( styles ) => setAttributes( { styles } ) }
								options={ styleList }
							/>
						}
						<VisuallyHidden>
							{
								!! styleList.length &&
								<MultipleCheckboxControl
									listItems={ styleSupported(styles) }
									selected={ styleSup }
									onItemChange={ supCheckChange }
									label = { __( 'Items supported by display style', 'display-post-types' ) }
								/>
							}
						</VisuallyHidden>
						{
							(styles && ifStyleSupport(styles, 'multicol')) &&
							<RangeControl
								label={ __( 'Maximum grid columns (Responsive)', 'display-post-types' ) }
								value={ colNarr }
								onChange={ ( colNarr ) => setAttributes( { colNarr } ) }
								min={ 1 }
								max={ 8 }
							/>
						}
						<RangeControl
							label={ __( 'Horizontal Gutter (in px)', 'display-post-types' ) }
							value={ hGutter }
							onChange={ ( hGutter ) => setAttributes( { hGutter } ) }
							min={ 0 }
							max={ 100 }
						/>
						<RangeControl
							label={ __( 'Vertical Gutter (in px)', 'display-post-types' ) }
							value={ vGutter }
							onChange={ ( vGutter ) => setAttributes( { vGutter } ) }
							min={ 0 }
							max={ 100 }
						/>
						{
							(styles && ifStyleSupport(styles, 'slider')) &&
							<RangeControl
								label={ __( 'Auto slide timer (delay in ms)', 'display-post-types' ) }
								value={ autoTime }
								onChange={ ( autoTime ) => setAttributes( { autoTime } ) }
								min={ 0 }
								max={10000}
								step={ 500 }
							/>
						}
					</PanelBody>
					<PanelBody initialOpen={ false } title={ __( 'Manage Display Elements', 'display-post-types' ) }>
						<PanelBody initialOpen={ false } title={ __( 'Item Wrapper', 'display-post-types' ) }>
							<SelectControl
								label={ __( 'Text Align', 'display-post-types' ) }
								value={ textAlign }
								onChange={ ( textAlign ) => setAttributes( { textAlign } ) }
								options={ [
									{ value: '', label: __( 'Left Align', 'display-post-types' ) },
									{ value: 'r-text', label: __( 'Right Align', 'display-post-types' ) },
									{ value: 'c-text', label: __( 'Center Align', 'display-post-types' ) },
								] }
							/>
							<RangeControl
								label={ __( 'Border Radius (in px)', 'display-post-types' ) }
								value={ brRadius }
								onChange={ ( brRadius ) => setAttributes( { brRadius } ) }
								min={ 0 }
								max={ 100 }
							/>
						</PanelBody>
						<div className="dpt-elem-container">
						{
							!! styleList.length &&
							styleSupported(styles).map( ( item ) => (
								<PanelBody initialOpen={ false } title={ item.label }>
									{getElement(item)}
								</PanelBody>
							) )
						}
						</div>
					</PanelBody>
				</InspectorControls>
				<div className="dpt-container" ref={this.elemRef}>
					<ServerSideRender
						block="dpt/display-post-types"
						attributes={ this.props.attributes }
					/>
				</div>
			</Fragment>
		);
	}

}

export default DisplayPostTypes;
