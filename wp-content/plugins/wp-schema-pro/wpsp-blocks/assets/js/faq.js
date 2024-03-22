function WPSPslideUp( wpsptarget, duration ) {
	wpsptarget.style.transitionProperty = 'height, margin, padding';
	wpsptarget.style.transitionDuration = duration + 'ms';
	wpsptarget.style.boxSizing = 'border-box';
	wpsptarget.style.height = wpsptarget.offsetHeight + 'px';
	wpsptarget.style.overflow = 'hidden';
	wpsptarget.style.height = 0;
	wpsptarget.style.paddingTop = 0;
	wpsptarget.style.paddingBottom = 0;
	wpsptarget.style.marginTop = 0;
	wpsptarget.style.marginBottom = 0;
	window.setTimeout( function () {
		wpsptarget.style.display = 'none';
		wpsptarget.style.removeProperty( 'height' );
		wpsptarget.style.removeProperty( 'padding-top' );
		wpsptarget.style.removeProperty( 'padding-bottom' );
		wpsptarget.style.removeProperty( 'margin-top' );
		wpsptarget.style.removeProperty( 'margin-bottom' );
		wpsptarget.style.removeProperty( 'overflow' );
		wpsptarget.style.removeProperty( 'transition-duration' );
		wpsptarget.style.removeProperty( 'transition-property' );
	}, duration );
}

function WPSPslideDown( wpsptarget, duration ) {
	wpsptarget.style.removeProperty( 'display' );
	let display = window.getComputedStyle( wpsptarget ).display;

	if ( display === 'none' ) display = 'block';

	wpsptarget.style.display = display;
	const height = wpsptarget.offsetHeight;
	wpsptarget.style.overflow = 'hidden';
	wpsptarget.style.height = 0;
	wpsptarget.style.paddingTop = 0;
	wpsptarget.style.paddingBottom = 0;
	wpsptarget.style.marginTop = 0;
	wpsptarget.style.marginBottom = 0;
	wpsptarget.style.boxSizing = 'border-box';
	wpsptarget.style.transitionProperty = 'height, margin, padding';
	wpsptarget.style.transitionDuration = duration + 'ms';
	wpsptarget.style.height = height + 'px';
	wpsptarget.style.removeProperty( 'padding-top' );
	wpsptarget.style.removeProperty( 'padding-bottom' );
	wpsptarget.style.removeProperty( 'margin-top' );
	wpsptarget.style.removeProperty( 'margin-bottom' );
	window.setTimeout( function () {
		wpsptarget.style.removeProperty( 'height' );
		wpsptarget.style.removeProperty( 'overflow' );
		wpsptarget.style.removeProperty( 'transition-duration' );
		wpsptarget.style.removeProperty( 'transition-property' );
	}, duration );
}

function setupFAQ() {
	const pattern = new RegExp( '^[\\w\\-]+$' );
	const hashval = window.location.hash.substring( 1 );
	const expandFirstelements = document.getElementsByClassName(
		'wpsp-faq-expand-first-true'
	);
	const inactiveOtherelements = document.getElementsByClassName(
		'wpsp-faq-inactive-other-false'
	);

	if (
		document.getElementById( hashval ) !== undefined &&
		document.getElementById( hashval ) !== null &&
		document.getElementById( hashval ) !== '' &&
		pattern.test( hashval )
	) {
		const elementToOpen = document.getElementById( hashval );
		if (
			elementToOpen.getElementsByClassName( 'wpsp-faq-item' )[ 0 ] !==
			undefined
		) {
			elementToOpen
				.getElementsByClassName( 'wpsp-faq-item' )[ 0 ]
				.classList.add( 'wpsp-faq-item-active' );
			elementToOpen
				.getElementsByClassName( 'wpsp-faq-item' )[ 0 ]
				.setAttribute( 'aria-expanded', true );
			WPSPslideDown(
				elementToOpen.getElementsByClassName( 'wpsp-faq-content' )[ 0 ],
				500
			);
		}
	} else {
		for ( let item = 0; item < expandFirstelements.length; item++ ) {
			if (
				true ===
				expandFirstelements[ item ].classList.contains(
					'wpsp-faq-layout-accordion'
				)
			) {
				expandFirstelements[ item ]
					.querySelectorAll( '.wpsp-faq-child__outer-wrap' )[ 0 ]
					.getElementsByClassName( 'wpsp-faq-item' )[ 0 ]
					.classList.add( 'wpsp-faq-item-active' );
				expandFirstelements[ item ]
					.querySelectorAll( '.wpsp-faq-child__outer-wrap' )[ 0 ]
					.getElementsByClassName( 'wpsp-faq-item' )[ 0 ]
					.setAttribute( 'aria-expanded', true );
				expandFirstelements[ item ]
					.querySelectorAll( '.wpsp-faq-child__outer-wrap' )[ 0 ]
					.getElementsByClassName( 'wpsp-faq-item' )[ 0 ]
					.querySelectorAll(
						'.wpsp-faq-content'
					)[ 0 ].style.display = 'block';
			}
		}
	}
	for (
		let otherItem = 0;
		otherItem < inactiveOtherelements.length;
		otherItem++
	) {
		if (
			true ===
			inactiveOtherelements[ otherItem ].classList.contains(
				'wpsp-faq-layout-accordion'
			)
		) {
			const otherItems = inactiveOtherelements[
				otherItem
			].querySelectorAll( '.wpsp-faq-child__outer-wrap' );

			for (
				let childItem = 0;
				childItem < otherItems.length;
				childItem++
			) {
				otherItems[ childItem ]
					.getElementsByClassName( 'wpsp-faq-item' )[ 0 ]
					.classList.add( 'wpsp-faq-item-active' );
				otherItems[ childItem ]
					.getElementsByClassName( 'wpsp-faq-item' )[ 0 ]
					.setAttribute( 'aria-expanded', true );
				otherItems[ childItem ]
					.getElementsByClassName( 'wpsp-faq-item' )[ 0 ]
					.querySelectorAll(
						'.wpsp-faq-content'
					)[ 0 ].style.display = 'block';
			}
		}
	}
}
/* eslint-disable */
window.addEventListener('load', function () {
    /* eslint-enable */
	setupFAQ();

	const accordionElements = document.getElementsByClassName(
		'wpsp-faq-layout-accordion'
	);
	for ( let item = 0; item < accordionElements.length; item++ ) {
		const questionButtons = accordionElements[ item ].querySelectorAll(
			'.wpsp-faq-questions-button'
		);

		for ( let button = 0; button < questionButtons.length; button++ ) {
			questionButtons[ button ].parentElement.addEventListener(
				'click',
				function ( e ) {
					WPSPfaqClick( e, this );
				}
			);
			questionButtons[ button ].parentElement.addEventListener(
				'keypress',
				function ( e ) {
					WPSPfaqClick( e, this );
				}
			);
		}
	}
} );

function WPSPfaqClick( e, wpspfaqItem ) {
	if ( e.target.tagName === 'A' ) {
		return;
	}
	e.preventDefault();
	if ( wpspfaqItem.classList.contains( 'wpsp-faq-item-active' ) ) {
		wpspfaqItem.classList.remove( 'wpsp-faq-item-active' );
		wpspfaqItem.setAttribute( 'aria-expanded', false );
		WPSPslideUp(
			wpspfaqItem.getElementsByClassName( 'wpsp-faq-content' )[ 0 ],
			500
		);
	} else {
		const parent = e.currentTarget.closest( '.wp-block-wpsp-faq' );
		let faqToggle = 'true';
		if ( parent.classList.contains( 'wp-block-wpsp-faq' ) ) {
			faqToggle = parent.getAttribute( 'data-faqtoggle' );
		}
		wpspfaqItem.classList.add( 'wpsp-faq-item-active' );
		wpspfaqItem.setAttribute( 'aria-expanded', true );
		WPSPslideDown(
			wpspfaqItem.getElementsByClassName( 'wpsp-faq-content' )[ 0 ],
			500
		);
		if ( 'true' === faqToggle ) {
			const questionButtons = parent.querySelectorAll(
				'.wpsp-faq-questions-button'
			);
			for (
				let buttonChild = 0;
				buttonChild < questionButtons.length;
				buttonChild++
			) {
				const buttonItem = questionButtons[ buttonChild ].parentElement;
				if ( buttonItem === wpspfaqItem ) {
					continue;
				}
				buttonItem.classList.remove( 'wpsp-faq-item-active' );
				buttonItem.setAttribute( 'aria-expanded', false );
				WPSPslideUp(
					buttonItem.getElementsByClassName(
						'wpsp-faq-content'
					)[ 0 ],
					500
				);
			}
		}
	}
}
