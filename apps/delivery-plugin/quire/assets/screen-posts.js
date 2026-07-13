/* Quire Posts — the pattern's behaviors:
 * · selection transforms the toolbar into the bulk bar (contextual reveal)
 * · quick edit + bulk edit are drawers over a scrim
 * · destructive = instant + Undo toast, no dialog, no reload
 * · views counts update live from every server response
 */
( function () {
	'use strict';

	var screen = document.querySelector( '.quire-screen' );
	if ( ! screen ) return;
	var AJAX  = screen.dataset.ajax;
	var NONCE = screen.dataset.nonce;

	function post( action, data ) {
		var body = new URLSearchParams();
		body.set( 'action', action );
		body.set( 'nonce', NONCE );
		Object.keys( data ).forEach( function ( k ) {
			if ( Array.isArray( data[ k ] ) ) {
				data[ k ].forEach( function ( v ) { body.append( k + '[]', v ); } );
			} else {
				body.set( k, data[ k ] );
			}
		} );
		return fetch( AJAX, { method: 'POST', credentials: 'same-origin', body: body } )
			.then( function ( r ) { return r.json(); } );
	}

	/* ---- views counts ------------------------------------------------- */
	function updateCounts( counts ) {
		if ( ! counts ) return;
		screen.querySelectorAll( '.qviews a' ).forEach( function ( a ) {
			var n = counts[ a.dataset.view ];
			if ( n === undefined ) return;
			a.querySelector( '.qviews__n' ).textContent = n;
			// self-pruning works live too: hide emptied views (never "All")
			if ( 'all' !== a.dataset.view && ! a.classList.contains( 'is-current' ) ) {
				a.style.display = n > 0 ? '' : 'none';
			}
		} );
	}

	/* ---- toast with optional undo -------------------------------------- */
	var toast = screen.querySelector( '.qtoast' );
	var toastTimer = null, undoFn = null;
	function showToast( msg, onUndo ) {
		clearTimeout( toastTimer );
		undoFn = onUndo || null;
		toast.querySelector( '.qtoast__msg' ).textContent = msg;
		toast.classList.toggle( 'no-undo', ! onUndo );
		toast.hidden = false;
		requestAnimationFrame( function () { toast.classList.add( 'is-open' ); } );
		toastTimer = setTimeout( hideToast, 8000 );
	}
	function hideToast() {
		toast.classList.remove( 'is-open' );
		undoFn = null;
	}
	toast.querySelector( '.qtoast__undo' ).addEventListener( 'click', function () {
		if ( undoFn ) undoFn();
		hideToast();
	} );

	/* ---- selection → bulk bar ------------------------------------------ */
	var tb = screen.querySelector( '.qtb' );
	var selectAll = document.getElementById( 'qselect-all' );
	function selectedRows() {
		return [].slice.call( screen.querySelectorAll( '.qrow__cb:checked' ) )
			.map( function ( cb ) { return cb.closest( '.qrow' ); } );
	}
	function refreshSelection() {
		var rows = selectedRows();
		screen.querySelectorAll( '.qrow' ).forEach( function ( r ) {
			r.classList.toggle( 'is-selected', rows.indexOf( r ) !== -1 );
		} );
		tb.dataset.mode = rows.length ? 'selected' : 'rest';
		tb.querySelector( '.qtb__selected' ).hidden = ! rows.length;
		if ( rows.length ) {
			tb.querySelector( '.qtb__count' ).textContent =
				rows.length + ' ' + ( rows.length === 1 ? 'selected' : 'selected' );
		}
	}
	screen.addEventListener( 'change', function ( e ) {
		if ( e.target.classList.contains( 'qrow__cb' ) ) refreshSelection();
		if ( e.target === selectAll ) {
			screen.querySelectorAll( '.qrow__cb' ).forEach( function ( cb ) { cb.checked = selectAll.checked; } );
			refreshSelection();
		}
	} );
	tb.querySelector( '.qtb__clear' ).addEventListener( 'click', function () {
		screen.querySelectorAll( '.qrow__cb:checked' ).forEach( function ( cb ) { cb.checked = false; } );
		if ( selectAll ) selectAll.checked = false;
		refreshSelection();
	} );

	/* ---- instant trash / restore / delete with Undo -------------------- */
	function rowIds( rows ) { return rows.map( function ( r ) { return JSON.parse( r.dataset.post ).id; } ); }

	function act( op, rows, label ) {
		var ids = rowIds( rows );
		rows.forEach( function ( r ) { r.classList.add( 'is-leaving' ); } );
		post( 'quire_post_action', { op: op, ids: ids } ).then( function ( res ) {
			if ( ! res.success ) { rows.forEach( function ( r ) { r.classList.remove( 'is-leaving' ); } ); return; }
			rows.forEach( function ( r ) {
				r.style.display = 'none';
				var cb = r.querySelector( '.qrow__cb' );
				if ( cb ) cb.checked = false; // hidden rows must leave the selection
			} );
			if ( selectAll ) selectAll.checked = false;
			updateCounts( res.data.counts );
			refreshSelection();
			var undo = null;
			if ( 'trash' === op || 'untrash' === op ) {
				var back = 'trash' === op ? 'untrash' : 'trash';
				undo = function () {
					post( 'quire_post_action', { op: back, ids: ids } ).then( function ( r2 ) {
						rows.forEach( function ( r ) { r.style.display = ''; r.classList.remove( 'is-leaving' ); } );
						if ( r2.success ) updateCounts( r2.data.counts );
					} );
				};
			}
			showToast( label, undo );
		} );
	}

	function rowTitle( row ) { return JSON.parse( row.dataset.post ).title; }

	screen.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '[data-act]' );
		if ( btn ) {
			var row = btn.closest( '.qrow' );
			var op  = btn.dataset.act;
			if ( 'quick' === op ) { openQuick( row ); return; }
			if ( 'delete' === op && ! window.confirm( 'Delete permanently? This cannot be undone.' ) ) return;
			var labels = {
				trash:   '“' + rowTitle( row ) + '” moved to Trash',
				untrash: '“' + rowTitle( row ) + '” restored',
				delete:  '“' + rowTitle( row ) + '” deleted permanently'
			};
			act( op, [ row ], labels[ op ] );
		}
		var bulk = e.target.closest( '[data-bulk]' );
		if ( bulk ) {
			var rows = selectedRows();
			if ( ! rows.length ) return;
			var op2 = bulk.dataset.bulk;
			if ( 'edit' === op2 ) { openBulk( rows ); return; }
			if ( 'delete' === op2 && ! window.confirm( 'Delete ' + rows.length + ' posts permanently? This cannot be undone.' ) ) return;
			var labels2 = {
				trash:   rows.length + ' posts moved to Trash',
				untrash: rows.length + ' posts restored',
				delete:  rows.length + ' posts deleted permanently'
			};
			act( op2, rows, labels2[ op2 ] );
		}
	} );

	var emptyTrash = document.getElementById( 'qempty-trash' );
	if ( emptyTrash ) {
		emptyTrash.addEventListener( 'click', function () {
			if ( ! window.confirm( 'Empty the Trash? All trashed posts are deleted permanently.' ) ) return;
			var rows = [].slice.call( screen.querySelectorAll( '.qrow' ) );
			act( 'delete', rows, 'Trash emptied' );
		} );
	}

	/* ---- drawers -------------------------------------------------------- */
	var scrim = screen.querySelector( '.qscrim' );
	var quick = document.getElementById( 'qquick' );
	var bulkD = document.getElementById( 'qbulk' );
	var current = null;   // row being quick-edited
	var bulkRows = [];

	function openDrawer( d ) { d.classList.add( 'is-open' ); scrim.classList.add( 'is-open' ); }
	function closeDrawers() {
		[ quick, bulkD ].forEach( function ( d ) { d.classList.remove( 'is-open' ); } );
		scrim.classList.remove( 'is-open' );
	}
	scrim.addEventListener( 'click', closeDrawers );
	screen.querySelectorAll( '.qdrawer__close, .qdrawer__cancel' ).forEach( function ( b ) {
		b.addEventListener( 'click', closeDrawers );
	} );

	function openQuick( row ) {
		current = row;
		var d = JSON.parse( row.dataset.post );
		quick.querySelector( '.qdrawer__sub' ).textContent = d.title;
		quick.querySelector( '[name=title]' ).value = d.title;
		quick.querySelector( '[name=slug]' ).value = d.slug;
		quick.querySelector( '[name=status]' ).value = 'private' === d.status ? 'publish' : d.status;
		quick.querySelector( '[name=date]' ).value = d.date;
		quick.querySelector( '[name=author]' ).value = d.author;
		quick.querySelector( '[name=visibility]' ).value = d.protected ? 'password' : ( 'private' === d.status ? 'private' : 'public' );
		quick.querySelector( '[name=password]' ).value = '';
		quick.querySelector( '[data-password]' ).hidden = ! d.protected;
		quick.querySelectorAll( '[name="cats[]"]' ).forEach( function ( cb ) {
			cb.checked = d.cats.indexOf( parseInt( cb.value, 10 ) ) !== -1;
		} );
		quick.querySelector( '[name=tags]' ).value = d.tags;
		quick.querySelector( '[name=comments]' ).checked = d.comments;
		quick.querySelector( '[name=pings]' ).checked = d.pings;
		quick.querySelector( '[name=sticky]' ).checked = d.sticky;
		openDrawer( quick );
	}
	quick.querySelector( '[name=visibility]' ).addEventListener( 'change', function () {
		quick.querySelector( '[data-password]' ).hidden = 'password' !== this.value;
	} );

	// after a save the server sends fresh row data — repaint the row in place
	function repaintRow( row, d ) {
		row.dataset.post = JSON.stringify( d );
		row.querySelector( '.qrow__title' ).textContent = d.title;
		row.querySelectorAll( '.qrow__line .badge' ).forEach( function ( b ) { b.remove(); } );
		var badges = [];
		if ( 'future' === d.status )  badges.push( 'Scheduled' );
		if ( 'draft' === d.status )   badges.push( 'Draft' );
		if ( 'pending' === d.status ) badges.push( 'Pending' );
		if ( 'private' === d.status ) badges.push( 'Private' );
		if ( d.protected )            badges.push( 'Protected' );
		if ( d.sticky )               badges.push( 'Sticky' );
		var line = row.querySelector( '.qrow__line' );
		badges.forEach( function ( b ) {
			var s = document.createElement( 'span' );
			s.className = 'badge badge--neutral';
			s.textContent = b;
			line.appendChild( s );
		} );
		row.querySelector( '.qcol--author' ).textContent = d.authorName;
		row.querySelector( '.qcell--cats' ).textContent = d.catNames || '—';
		row.querySelector( '.qcell--tags' ).textContent = d.tags || '—';
		row.querySelector( '.qdate__l1' ).textContent = d.dateL1;
		row.querySelector( '.qdate__l2' ).textContent = d.dateL2;
	}

	document.getElementById( 'qquick-save' ).addEventListener( 'click', function () {
		if ( ! current ) return;
		var before = current.dataset.post;
		var payload = {
			id: JSON.parse( before ).id,
			title: quick.querySelector( '[name=title]' ).value,
			slug: quick.querySelector( '[name=slug]' ).value,
			status: quick.querySelector( '[name=status]' ).value,
			date: quick.querySelector( '[name=date]' ).value,
			author: quick.querySelector( '[name=author]' ).value,
			visibility: quick.querySelector( '[name=visibility]' ).value,
			password: quick.querySelector( '[name=password]' ).value,
			tags: quick.querySelector( '[name=tags]' ).value,
			cats: [].slice.call( quick.querySelectorAll( '[name="cats[]"]:checked' ) ).map( function ( cb ) { return cb.value; } ),
			comments: quick.querySelector( '[name=comments]' ).checked ? 1 : '',
			pings: quick.querySelector( '[name=pings]' ).checked ? 1 : '',
			sticky: quick.querySelector( '[name=sticky]' ).checked ? 1 : ''
		};
		var row = current;
		post( 'quire_quick_save', payload ).then( function ( res ) {
			if ( ! res.success ) return;
			closeDrawers();
			repaintRow( row, res.data.row );
			updateCounts( res.data.counts );
			var prev = JSON.parse( before );
			showToast( '“' + res.data.row.title + '” updated', function () {
				post( 'quire_quick_save', {
					id: prev.id, title: prev.title, slug: prev.slug,
					status: 'private' === prev.status ? 'publish' : prev.status,
					date: prev.date, author: prev.author,
					visibility: prev.protected ? 'password' : ( 'private' === prev.status ? 'private' : 'public' ),
					password: '', tags: prev.tags, cats: prev.cats,
					comments: prev.comments ? 1 : '', pings: prev.pings ? 1 : '',
					sticky: prev.sticky ? 1 : ''
				} ).then( function ( r2 ) {
					if ( r2.success ) { repaintRow( row, r2.data.row ); updateCounts( r2.data.counts ); }
				} );
			} );
		} );
	} );

	/* ---- bulk edit drawer ----------------------------------------------- */
	function openBulk( rows ) {
		bulkRows = rows;
		bulkD.querySelector( '.qdrawer__title' ).textContent = 'Bulk edit — ' + rows.length + ' posts';
		bulkD.querySelector( '.qdrawer__sub' ).textContent =
			rows.map( rowTitle ).slice( 0, 3 ).join( ' · ' ) + ( rows.length > 3 ? ' · …' : '' );
		document.getElementById( 'qbulk-save' ).textContent = 'Update ' + rows.length + ' posts';
		bulkD.querySelectorAll( 'input[type=checkbox]' ).forEach( function ( cb ) { cb.checked = false; } );
		bulkD.querySelectorAll( 'select' ).forEach( function ( s ) { s.value = ''; } );
		bulkD.querySelectorAll( 'input.field' ).forEach( function ( f ) { f.value = ''; } );
		openDrawer( bulkD );
	}
	document.getElementById( 'qbulk-save' ).addEventListener( 'click', function () {
		var rows = bulkRows;
		post( 'quire_bulk_edit', {
			ids: rowIds( rows ),
			add_cats: [].slice.call( bulkD.querySelectorAll( '[name="add_cats[]"]:checked' ) ).map( function ( cb ) { return cb.value; } ),
			remove_cats: [].slice.call( bulkD.querySelectorAll( '[name="remove_cats[]"]:checked' ) ).map( function ( cb ) { return cb.value; } ),
			add_tags: bulkD.querySelector( '[name=add_tags]' ).value,
			remove_tags: bulkD.querySelector( '[name=remove_tags]' ).value,
			status: bulkD.querySelector( '[name=status]' ).value,
			author: bulkD.querySelector( '[name=author]' ).value,
			comments: bulkD.querySelector( '[name=comments]' ).value,
			sticky: bulkD.querySelector( '[name=sticky]' ).value
		} ).then( function ( res ) {
			if ( ! res.success ) return;
			closeDrawers();
			res.data.rows.forEach( function ( d ) {
				rows.forEach( function ( r ) {
					if ( JSON.parse( r.dataset.post ).id === d.id ) repaintRow( r, d );
				} );
			} );
			updateCounts( res.data.counts );
			tb.querySelector( '.qtb__clear' ).click();
			showToast( res.data.rows.length + ' posts updated', null );
		} );
	} );
} )();
