/* Quire Dashboard — customize mode (Figma H1 + H2c + H3).
 *
 * At rest: zero drag chrome (H1). "Customize" reveals only an × per
 * widget plus a dashed "+ Add widget" slot per column; the WHOLE CARD is
 * the drag surface (H2c — the Trello/iOS-widget pattern) and card content
 * goes inert for the duration of the mode. The picker drawer (H3) lists
 * every widget with Add/Remove; remove only hides, add brings the
 * already-rendered card back from the hidden store. Every change
 * auto-saves per user (no separate save step, matches core).
 */
( function () {
	'use strict';

	var screen = document.querySelector( '.quire-screen' );
	if ( ! screen ) return;

	/* ---- Quick draft: collapsed until focused ------------------------ */
	var draft = screen.querySelector( '.qdraft' );
	if ( draft ) {
		var dTitle = draft.querySelector( 'input[name="quire_draft_title"]' );
		var dBody  = draft.querySelector( 'textarea[name="quire_draft_content"]' );
		dTitle.addEventListener( 'focus', function () { draft.classList.remove( 'is-collapsed' ); } );
		if ( dTitle.value ) draft.classList.remove( 'is-collapsed' );
		draft.querySelector( '.qdraft__discard' ).addEventListener( 'click', function () {
			dTitle.value = '';
			dBody.value  = '';
			draft.classList.add( 'is-collapsed' );
		} );
	}

	/* ---- customize mode ---------------------------------------------- */
	var btn    = document.getElementById( 'qcustomize' );
	var drawer = screen.querySelector( '.qdrawer' );
	var scrim  = screen.querySelector( '.qscrim' );
	var store  = screen.querySelector( '.qhiddenstore' );
	var cols   = {
		main: screen.querySelector( '.qcol[data-col="main"]' ),
		side: screen.querySelector( '.qcol[data-col="side"]' )
	};
	if ( ! btn || ! drawer || ! store ) return;

	var targetCol = cols.main; // column whose "+ Add widget" opened the drawer

	function customizing() {
		return screen.classList.contains( 'is-customizing' );
	}

	function setDraggable( on ) {
		screen.querySelectorAll( '.qcol .qwidget' ).forEach( function ( card ) {
			card.draggable = on;
		} );
	}

	btn.addEventListener( 'click', function () {
		var on = screen.classList.toggle( 'is-customizing' );
		btn.textContent = on ? btn.dataset.labelDone : btn.dataset.label;
		setDraggable( on );
		if ( ! on ) closeDrawer();
	} );

	/* ---- persistence -------------------------------------------------- */
	function colIds( col ) {
		return Array.prototype.map.call(
			col.querySelectorAll( '.qwidget' ),
			function ( el ) { return el.dataset.widget; }
		);
	}

	function save() {
		if ( dragged ) return; // never persist a half-finished drag
		var body = new URLSearchParams();
		body.set( 'action', 'quire_dashboard_layout' );
		body.set( 'nonce', screen.dataset.nonce );
		body.set( 'layout', JSON.stringify( {
			main:   colIds( cols.main ),
			side:   colIds( cols.side ),
			hidden: colIds( store )
		} ) );
		fetch( screen.dataset.ajax, { method: 'POST', credentials: 'same-origin', body: body } );
	}

	/* ---- drag to rearrange (whole card, customize mode only) ---------- */
	var dragged = null;
	var ghost   = document.createElement( 'div' );
	ghost.className = 'qghost';

	screen.addEventListener( 'dragstart', function ( e ) {
		var card = e.target.closest( '.qwidget' );
		if ( ! card || ! customizing() || dragged ) { e.preventDefault(); return; }
		dragged = card;
		e.dataTransfer.effectAllowed = 'move';
		e.dataTransfer.setData( 'text/plain', card.dataset.widget );
		ghost.style.height = Math.min( card.offsetHeight, 180 ) + 'px';
		// hide after the browser has captured the drag image
		setTimeout( function () {
			card.parentNode.insertBefore( ghost, card );
			card.style.display = 'none';
		}, 0 );
	} );

	screen.addEventListener( 'dragend', function () {
		if ( ! dragged ) return;
		// drop outside a column: put the card back where the ghost is
		finishDrop();
	} );

	function finishDrop() {
		if ( ghost.parentNode ) {
			ghost.parentNode.insertBefore( dragged, ghost );
			ghost.parentNode.removeChild( ghost );
		}
		dragged.style.display = '';
		dragged.draggable = customizing();
		dragged = null;
		save();
	}

	Object.keys( cols ).forEach( function ( key ) {
		var col = cols[ key ];
		col.addEventListener( 'dragover', function ( e ) {
			if ( ! dragged ) return;
			e.preventDefault();
			e.dataTransfer.dropEffect = 'move';
			var after = null;
			var cards = col.querySelectorAll( '.qwidget' );
			for ( var i = 0; i < cards.length; i++ ) {
				if ( cards[ i ] === dragged ) continue;
				var r = cards[ i ].getBoundingClientRect();
				if ( e.clientY < r.top + r.height / 2 ) { after = cards[ i ]; break; }
			}
			var slot = col.querySelector( '.qslot' );
			col.insertBefore( ghost, after || slot );
		} );
		col.addEventListener( 'drop', function ( e ) {
			if ( ! dragged ) return;
			e.preventDefault();
			finishDrop();
		} );
	} );

	/* ---- remove (×) ---------------------------------------------------- */
	screen.addEventListener( 'click', function ( e ) {
		var x = e.target.closest( '.qremove' );
		if ( ! x || ! customizing() ) return;
		var card = x.closest( '.qwidget' );
		if ( 'welcome' === card.dataset.widget ) {
			// Welcome's × IS the dismissal — final, not in the picker.
			fetch( screen.dataset.dismiss, { credentials: 'same-origin' } );
			card.remove();
			return;
		}
		store.appendChild( card );
		refreshDrawer();
		save();
	} );

	/* ---- picker drawer -------------------------------------------------- */
	function refreshDrawer() {
		drawer.querySelectorAll( '.qdrawer__row' ).forEach( function ( row ) {
			var id     = row.dataset.widget;
			var hidden = !! store.querySelector( '.qwidget[data-widget="' + id + '"]' );
			row.querySelector( '.qdrawer__add' ).hidden        = ! hidden;
			row.querySelector( '.qdrawer__removelink' ).hidden = hidden;
		} );
	}

	function openDrawer() {
		refreshDrawer();
		drawer.classList.add( 'is-open' );
		scrim.classList.add( 'is-open' );
	}

	function closeDrawer() {
		drawer.classList.remove( 'is-open' );
		scrim.classList.remove( 'is-open' );
	}

	screen.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '.qslot' ) ) {
			targetCol = e.target.closest( '.qcol' );
			openDrawer();
		}
		if ( e.target.closest( '.qdrawer__close' ) || e.target === scrim ) closeDrawer();
	} );

	drawer.addEventListener( 'click', function ( e ) {
		var row = e.target.closest( '.qdrawer__row' );
		if ( ! row ) return;
		var id   = row.dataset.widget;
		var card = screen.querySelector( '.qwidget[data-widget="' + id + '"]' );
		if ( ! card ) return;
		if ( e.target.closest( '.qdrawer__add' ) ) {
			targetCol.insertBefore( card, targetCol.querySelector( '.qslot' ) );
			card.draggable = customizing();
		} else if ( e.target.closest( '.qdrawer__removelink' ) ) {
			store.appendChild( card );
		} else {
			return;
		}
		refreshDrawer();
		save();
	} );
} )();
