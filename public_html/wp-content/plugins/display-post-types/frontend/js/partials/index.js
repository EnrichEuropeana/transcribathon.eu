import DPT from './dpt';

/**
 * Initialize post types display
 * 
 * @since 1.0.0
 */
class DisplayPostTypes {

	/**
	 * The constructor function.
	 *
	 * @since 1.0.0
	 */
	constructor() {
        const _this = this;
		this.elems = Array.prototype.slice.call(document.querySelectorAll('.dpt-wrapper'));
		this.elems.forEach(elem => { this.createdpt(elem); } );

        document.addEventListener(
            'animationstart',
            function(e) {
                if ('dptAdded' !== e.animationName) { return; }

                const elem = e.target;

                // Return if element is not correct.
                if (!elem.classList.contains('dpt-wrapper')) { return; }

                // Return if element is already processed.
                if (elem.classList.contains('dpt-added')) { return; }
        
                _this.createdpt(elem);
            },
            false
        );
    }
    
    /**
	 * Script initialize.
	 * 
	 * @since 1.0.0
     *
     * @param Object elem
	 */
	createdpt(elem) {
        elem.classList.add('dpt-added');
        new DPT(elem);
	}
}

export default DisplayPostTypes;