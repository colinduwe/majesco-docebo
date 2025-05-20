import 'bootstrap/js/src/util';
import 'bootstrap/js/src/modal';

var courseModal = document.getElementById('majesco_course-modal')

function getAdjacentCards(card, postType){
	let adjacentCards = {
		'nextID': null,
		'prevID': null
	}
	let cardWrapper = card.closest('.col');
	if( cardWrapper.previousElementSibling ){
		adjacentCards.prevID = cardWrapper.previousElementSibling.querySelector('.majesco-2022-read-more-link').getAttribute('data-post-id');
	}
	if( cardWrapper.nextElementSibling ){
		adjacentCards.nextID = cardWrapper.nextElementSibling.querySelector('.majesco-2022-read-more-link').getAttribute('data-post-id');
	}
	return adjacentCards;
}
function hookPrevNext(modalContent){
	let prevCardButton = modalContent.querySelector('#modal-previous-card');
	let nextCardButton = modalContent.querySelector('#modal-next-card');
	let objectType = modalContent.getAttribute('data-object-type');
	if( prevCardButton ){
	prevCardButton.addEventListener( 'click', event => {
			let prevCardID = prevCardButton.getAttribute('data-post-id');
			if(prevCardID != 0){
				let prevCard = document.getElementById( objectType + '-' + prevCardID).querySelector('a');
				loadCardIntoModal(prevCard);
			}
		});
	}
	if( nextCardButton ){
		nextCardButton.addEventListener( 'click', event => {
			let nextCardID = nextCardButton.getAttribute('data-post-id');
			if(nextCardID != 0){
				let nextCard = document.getElementById(objectType + '-' + nextCardID).querySelector('a');
				loadCardIntoModal(nextCard);
			}
		});
	}
}
function loadCardIntoModal(card){
	var id = card.getAttribute('data-post-id')
	var postType = card.getAttribute('data-post-type')
	var modalTarget = card.getAttribute('data-bs-target');
	var nonce = card.getAttribute('data-nonce');
	const httpRequest = new XMLHttpRequest();
	const url = majesco_docebo_ajax_load_more.ajaxurl;
	const adjacentCards = getAdjacentCards(card, postType);
	const data = {
		'modalType': 'majesco_course',
		'postType': postType,
		'id': id,
		'nextID': adjacentCards.nextID,
		'prevID': adjacentCards.prevID
	}
	httpRequest.onreadystatechange = () => {
		if (httpRequest.readyState === XMLHttpRequest.DONE) {
			if (httpRequest.status === 200) {
				let frag = document.createRange().createContextualFragment( httpRequest.responseText );
				let modal = document.querySelector(modalTarget);
				let modalContent = modal.querySelector('.modal-content');
				modalContent.replaceChildren( frag );
				//customerModal.handleUpdate();
				cdbootstrap.loadMore.init();
				hookPrevNext(modalContent);
			} else {
				console.log("There was a problem with the request.");
			}
		}
	}
	httpRequest.open( 'POST', url, true )
	httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
	httpRequest.send('action=majesco_docebo_ajax_modals&data=' + JSON.stringify( data ) + '&nonce=' + nonce);
}

if( courseModal != null ){
	courseModal.addEventListener('show.bs.modal', function (event) {
		  // Button that triggered the modal
		  var course = event.relatedTarget
		  loadCardIntoModal(course);
	})
}
