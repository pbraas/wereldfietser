/* global phpbb */

(function($) {  // Avoid conflicts with other libraries

'use strict';

function removeGivenCounters() {
	$('[data-user-give-id]').remove();
	$('dd.profile-posts a[href*="give=true"]').closest('dd').remove();
	$('.postprofile dd').filter(function() {
		return /has thanked|duimpje\s+gegeven|duimpjes\s+gegeven/i.test($(this).text());
	}).remove();
}

// Remove any stale "given/has thanked" counters rendered from old cache/output.
removeGivenCounters();

phpbb.addAjaxCallback('handle_thanks', function(res) {

	var mode = res.mode;
	var postContentId = '#post_content' + res.post_id;
	var postReputId = '#div_post_reput' + res.post_id;
	var givenTitle = '<strong>' + (res.given_count === 1 ? res.l_given_one : res.l_given_many) + res.l_colon + '</strong> ';
	var receivedTitle = '<strong><span class="thanks-thumb-picture" aria-hidden="true"></span><span class="sr-only">' + res.l_received + res.l_colon + '</span></strong> ';
	var thanksLinkId = '[id="lnk_thanks_post' + res.post_id + '"]';
	var thanksListId = '[id="list_thanks' + res.post_id + '"]';
	var userGiveId = '[data-user-give-id="' + res.from_id + '"]';
	var userReceiveId = '[data-user-receive-id="' + res.post_id + '"]';

	var l_thanks_given = res.l_thanks_given;

	var u_given = '<a href="' + res.u_given + '">' + l_thanks_given + '</a>';

	var thanksLink = $(thanksLinkId);
	var thanksList = $(thanksListId);
	var userGive = $(userGiveId);
	var userReceive = $(userReceiveId);
	userGive.remove();

	thanksLink.blur();
	$('i',thanksLink).removeClass(mode == 'insert' ? 'fa-thumbs-o-up' : 'fa-thumbs-o-down').addClass(mode == 'insert' ? "fa-thumbs-o-down" : 'fa-thumbs-o-up');
	$('span', thanksLink).text(mode == 'insert' ? res.l_remove_thanks_short : res.l_thank_post_short);

	thanksLink.each(function() {
		this.href = this.href.replace(mode == 'insert' ? 'thanks' : 'rthanks', mode == 'insert' ? 'rthanks' : 'thanks');
		this.title = this.title.replace((mode == 'insert') ? res.l_thank_post : res.l_remove_thanks, (mode == 'insert') ? res.l_remove_thanks : res.l_thank_post);
	})

	if (mode == 'insert') { // Handle thanks addition
		givenTitle = '<strong>' + (res.given_count === 1 ? res.l_given_one : res.l_given_many) + res.l_colon + '</strong> ';
		if (res.received_count >= 1) { // Handle received thanks count in posts miniprofiles
			userReceive.html(receivedTitle + '<span class="thanks-count-number">' + res.received_count + '</span>');
		} else {
			userReceive.html('');
		}

		if (res.given_count >= 1) { // Handle given thanks count in posts miniprofiles
			userGive.html(givenTitle + u_given);
		}

		if (!res.s_remove_thanks) { // Remove un-thank button if thanks removal is not allowed
			thanksLink.parent('li').remove();
		}
	} else 	if (mode == 'delete') { // Handle thanks deletion
		givenTitle = '<strong>' + (res.given_count === 1 ? res.l_given_one : res.l_given_many) + res.l_colon + '</strong> ';
		if (res.received_count == 0) { // Handle received thanks count in posts miniprofiles
			userReceive.html('');
		} else {
			userReceive.html(receivedTitle + '<span class="thanks-count-number">' + res.received_count + '</span>');
		}

		if (res.given_count == 0) { // Handle given thanks count in posts miniprofiles
			userGive.html('');
		} else {
			userGive.html(givenTitle + u_given);
		}
	}

	// Handle posts thanker lists and ratings while preserving each block position
	var htmlWrapper = $('<div>' + res.html + '</div>');
	var newThanksList = $(thanksListId, htmlWrapper);
	var newPostReput = $(postReputId, htmlWrapper);

	if (thanksList.length && newThanksList.length) {
		thanksList.replaceWith(newThanksList);
	}

	if (newPostReput.length) {
		$(postReputId).replaceWith(newPostReput);
	}

	// Refresh existing post ratings on the page if leader post rating changes
	if (!$.isEmptyObject(res.post_reput_html)) {
		$.each(res.post_reput_html, function(key, value) {
			var postReputId = '#div_post_reput' + key;
			$(postReputId).replaceWith(value);
		});
	}

	removeGivenCounters();
});

})(jQuery); // Avoid conflicts with other libraries
