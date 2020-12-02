jQuery.event.special.touchstart = { setup: function( _, ns, handle ){ this.addEventListener("touchstart", handle, { passive: true }) } };

$(document).undelegate('.agree', 'click');

$(document).delegate('.agree', 'click', function(e) {
	e.preventDefault();

	$('#modal-agree').remove();

	var element = this;

	$.ajax({
		url: $(element).attr('href'),
		type: 'get',
		dataType: 'html',
		success: function(data) {
			html  = '<div id="modal-agree" class="modal">';
			html += '  <div class="modal-dialog">';
			html += '    <div class="modal-content">';
			html += '      <div class="modal-header no-gutters">';
			html += '        <div class="col"><h5 class="modal-title">' + $(element).text() + '</h5></div><div class="col-auto"><div class="ft-icon-24 text-gray-500 ml-2" data-dismiss="modal"><svg height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path></svg></div></div>';
			html += '      </div>';
			html += '      <div class="modal-body">' + data + '</div>';
			html += '    </div>';
			html += '  </div>';
			html += '</div>';

			$('body').append(html);

			$('#modal-agree').modal('show');
		}
	});
});

var cart = {
	'add': function(product_id, quantity) {
		$.ajax({
			url: 'index.php?route=extension/module/framefreetheme/ff_cart/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				var loading_text = $('#ff_cart > button').attr('data-loading');
				$('#ff_cart').addClass('loading');
				$('#ff_cart > button').attr('disabled', 'disabled');
				old_cart = $('#ff_cart > button #ff_cart_total').html();
				$('#ff_cart > button #ff_cart_total').html('<span class="loading-wrapper">' + loading_text + '</span>');

			},
			success: function(json) {

				if (json['redirect']) {
					// location = json['redirect'];

					$('#ff_cart').removeClass('loading');
					$('#ff_cart > button #ff_cart_total').html(old_cart);
					$('#ff_cart > button').removeAttr('disabled');


					setTimeout(function () {
						$.ajax({
							url: 'index.php?route=extension/module/framefreetheme/ff_qoptions&product_id=' + product_id,
							type: 'post',
							dataType: 'html',
							beforeSend: function() {

								$('#ff_modal_qoptions, .modal-backdrop').remove();

								html  = '<div id="ff_modal_qoptions" class="modal fade" tabindex="-1">';
								html += '  <div class="modal-dialog modal-dialog-centered">';
								html += '    <div class="modal-content" id="qoptions-product-' + product_id + '">';
								html += '      <div class="modal-load-mask text-center p-5 text-muted">';
								html += '        <div class="modal-load-mask text-muted d-flex justify-content-center align-items-center py-4">';
								html += '					 <div class="spinner-border text-gray-300"></div>';
								html += '    	   </div>';
								html += '    	 </div>';
								html += '    </div>';
								html += '  </div>';
								html += '</div>';

								$('body').append(html);
								if (typeof add_modal_listner == 'function') { add_modal_listner('#ff_modal_qoptions') }

								$('#ff_modal_qoptions').modal('show');

							},
							success: function(data) {
								$('#ff_modal_qoptions .modal-content').html(data);
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					}, 100);

				} else if (json['success']) {
					$('#ff_popup_cart .alert').remove();
					$('#ff_popup_cart').modal('show');
					setTimeout(function () {

						if ( document.querySelector('#checkout-cart, #checkout-checkout') != null) location = location.href;

						$.ajax({
						url: 'index.php?route=extension/module/framefreetheme/ff_cart/info',
						type: 'post',
						dataType: 'html',
						complete: function() {
							$('#ff_cart').removeClass('loading');
							$('#ff_cart > button').removeAttr('disabled');
						},
						success: function(data){
							var data_alert 	= '<div class="alert alert-light mt-n3 mx-n3 px-3 border-bottom">';
									data_alert += 	'<div class="row no-gutters">';
									data_alert += 		'<div class="col-auto">';
									data_alert += 			'<i class="fa fa-fw fa-check mr-2"></i>';
									data_alert += 		'</div>';
									data_alert += 		'<div class="col">';
									data_alert += 			json['success'];
									data_alert += 		'</div>';
									data_alert += 		'<div class="col-auto">';
									data_alert += 			'<button type="button" class="close mr-1" data-dismiss="alert">&times;</button>';
									data_alert += 		'</div>';
									data_alert += 	'</div>';
									data_alert += '</div>';

							$('#ff_cart .cart-list').before(data_alert);
							$('#ff_cart > button #ff_cart_total').html(json['total']);

              $('#ff_m_cart_total').html($('#ff_cart > button #ff_cart_total .products > b').text());

							$('#ff_cart .cart-list').html($(data).find('.cart-list').html());
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
					}, 100);
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'update': function(key, quantity) {
		$.ajax({
			url: 'index.php?route=extension/module/framefreetheme/ff_cart/edit',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
			setTimeout(function () {
					var loading_text = $('#ff_cart > button').attr('data-loading');
					$('#ff_cart').addClass('loading');
					$('#ff_cart > button').attr('disabled', 'disabled');
					$('#ff_cart > button #ff_cart_total').html('<span class="loading-wrapper">' + loading_text + '</span>');
					$('#ff_popup_cart').find('.alert').remove();
				}, 99);
			},
			success: function(json) {
				setTimeout(function () {
					if ( document.querySelector('#checkout-cart, #checkout-checkout') != null) location = location.href;

					$.ajax({
						url: 'index.php?route=extension/module/framefreetheme/ff_cart/info',
						type: 'post',
						dataType: 'html',
						complete: function() {
							$('#ff_cart').removeClass('loading');
							$('#ff_cart > button').removeAttr('disabled');
						},
						success: function(data){
							$('#ff_cart > button #ff_cart_total').html(json['total']);

              $('#ff_m_cart_total').html($('#ff_cart > button #ff_cart_total .products > b').text());

							$('#ff_cart .cart-list').html($(data).find('.cart-list').html());
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});

				}, 100);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'updatePopup': function(key, quantity) {

		$.ajax({
			url: 'index.php?route=extension/module/framefreetheme/ff_cart/editPopup',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				setTimeout(function () {
					var loading_text = $('#ff_cart > button').attr('data-loading');
					$('#ff_cart').addClass('loading');
					$('#ff_cart > button').attr('disabled', 'disabled');
					$('#ff_cart > button #ff_cart_total').html('<span class="loading-wrapper">' + loading_text + '</span>');
					$('#ff_popup_cart').find('.alert').remove();
				}, 99);
			},
			success: function(json) {
				setTimeout(function () {
					if ( document.querySelector('#checkout-cart, #checkout-checkout') != null) location = location.href;

					$.ajax({
						url: 'index.php?route=extension/module/framefreetheme/ff_cart/info',
						type: 'post',
						dataType: 'html',
						complete: function() {
							$('#ff_cart').removeClass('loading');
							$('#ff_cart > button').removeAttr('disabled');
						},
						success: function(data){
							$('#ff_cart > button #ff_cart_total').html(json['total']);

              $('#ff_m_cart_total').html($('#ff_cart > button #ff_cart_total .products > b').text());

							$('#ff_cart .cart-list').html($(data).find('.cart-list').html());
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});

				}, 100);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=extension/module/framefreetheme/ff_cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				setTimeout(function () {
					var loading_text = $('#ff_cart > button').attr('data-loading');
					$('#ff_cart').addClass('loading');
					$('#ff_cart > button').attr('disabled', 'disabled');
					$('#ff_cart > button #ff_cart_total').html('<span class="loading-wrapper">' + loading_text + '</span>');
					$('#ff_popup_cart').find('.alert').remove();
				}, 99);
			},
			success: function(json) {
				setTimeout(function () {

					if ( document.querySelector('#checkout-cart, #checkout-checkout') != null) location = location.href;

					$.ajax({
						url: 'index.php?route=extension/module/framefreetheme/ff_cart/info',
						type: 'post',
						dataType: 'html',
						complete: function() {
							$('#ff_cart').removeClass('loading');
							$('#ff_cart > button').removeAttr('disabled');
						},
						success: function(data){
							$('#ff_cart > button #ff_cart_total').html(json['total']);

              $('#ff_m_cart_total').html($('#ff_cart > button #ff_cart_total .products > b').text());

							$('#ff_cart .cart-list').html($(data).find('.cart-list').html());
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});

				}, 100);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var voucher = {
	'add': function() {},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=extension/module/framefreetheme/ff_cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				setTimeout(function () {
					var loading_text = $('#ff_cart > button').attr('data-loading');
					$('#ff_cart').addClass('loading');
					$('#ff_cart > button').attr('disabled', 'disabled');
					$('#ff_cart > button #ff_cart_total').html('<span class="loading-wrapper">' + loading_text + '</span>');
					$('#ff_popup_cart').find('.alert').remove();
				}, 99);
			},
			success: function(json) {
				setTimeout(function () {
					if ( document.querySelector('#checkout-cart, #checkout-checkout') != null) location = location.href;

					$.ajax({
						url: 'index.php?route=extension/module/framefreetheme/ff_cart/info',
						type: 'post',
						dataType: 'html',
						complete: function() {
							$('#ff_cart').removeClass('loading');
							$('#ff_cart > button').removeAttr('disabled');
						},
						success: function(data){
							$('#ff_cart > button #ff_cart_total').html(json['total']);

              $('#ff_m_cart_total').html($('#ff_cart > button #ff_cart_total .products > b').text());

							$('#ff_cart .cart-list').html($(data).find('.cart-list').html());
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});

				}, 100);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('body').prepend('<div style="width: 300px;position: fixed;z-index:9999; top: 10px;right: 10px;"><div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div></div>');
				}

				$('#wishlist-total').html(json['total']);
				$('#wishlist-total').attr('title', json['total']);

				// $('html, body').animate({ scrollTop: 0 }, 'slow');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
}

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['success']) {
					$('body').prepend('<div style="width: 300px;position: fixed;z-index:9999; top: 10px;right: 10px;"><div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div></div>');

					$('#compare-total').html(json['total']);

					// $('html, body').animate({ scrollTop: 0 }, 'slow');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
}

var ftSearch = {
	'search': function(button) {

			var url = 'index.php?route=product/search',
					request = button.parentNode.parentNode.querySelector('[name=\'search\']'),
					category = button.parentNode.parentNode.querySelector('[name=\'category_id\']'),
					description = button.parentNode.parentNode.querySelector('[name=\'description\']'),
					sub_category = button.parentNode.parentNode.querySelector('[name=\'sub_category\']');


			if (request != null) url += '&search=' + encodeURIComponent(request.value);
			if (category != null && category.value > 0) url += '&category_id=' + encodeURIComponent(category.value);

			if (sub_category != null && sub_category.value) {
				url += '&sub_category=' + encodeURIComponent(sub_category.value);
			} else {
				url += '&sub_category=true';
			}

			if (description != null && description.value) url += '&sub_category=' + encodeURIComponent(description_check.value);

			if (request.value.length) location = url;

	},
	'key_enter': function(e) {

		if (e.keyCode == 13) {
			var button = e.target.parentNode.parentNode.querySelector('.search-button');
			ftSearch.search(button);
		}

	},
	'category_select': function(e, category_id) {

		var items = e.target.parentNode.querySelectorAll('.dropdown-item'),
				category = e.target.parentNode.parentNode.parentNode.querySelector('[name=\'category_id\']'),
				select_label = e.target.parentNode.parentNode.parentNode.querySelector('.select-text');

		if (select_label != null) select_label.innerHTML =  e.target.textContent;
		items.forEach(function(item, i) { item.classList.remove('active') });
		e.target.classList.add('active');
		category.value = category_id;

	}
}

var ff_countupd = (step, minimum, field) => {

	var count = parseInt($(field).val()) + Number(step);

	count = count < Number(minimum) ? Number(minimum) : count;

	$(field).val(count);
	$(field).change();

	return false;
}

var ff_qview = (product_id) => {

	$.ajax({
		url: 'index.php?route=extension/module/framefreetheme/ff_qview&product_id=' + product_id,
		type: 'post',
		dataType: 'html',
		beforeSend: function() {

			$('#ff_modal_qview, .modal-backdrop').remove();

			html  = '<div id="ff_modal_qview" class="modal fade" tabindex="-1">';
			html += '  <div class="modal-dialog modal-dialog-centered modal-lg">';
			html += '    <div class="modal-content" id="qview-product-' + product_id + '">';
			html += '      <div class="modal-load-mask text-muted d-flex justify-content-center align-items-center py-5 my-5">';
			html += '					<div class="spinner-border text-gray-300"></div>';
			html += '    	 </div>';
			html += '    </div>';
			html += '  </div>';
			html += '</div>';

			$('body').append(html);

			if (typeof add_modal_listner == 'function') add_modal_listner('#ff_modal_qview', 'quick-view-' + product_id );

			$('#ff_modal_qview').modal('show');

		},
		success: function(data) {
			$('#ff_modal_qview .modal-content').html(data);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

var ff_fastorder = (product_id) => {

	$.ajax({
		url: 'index.php?route=extension/module/framefreetheme/ff_fastorder&product_id=' + product_id,
		type: 'post',
		dataType: 'html',
		beforeSend: function() {

			$('#ff_modal_fastorder, .modal-backdrop').remove();

			html  = '<div id="ff_modal_fastorder" class="modal fade" tabindex="-1" role="dialog">';
			html += '  <div class="modal-dialog modal-dialog-centered" role="document">';
			html += '    <div class="modal-content" id="fastorder-product-' + product_id + '">';
			html += '      <div class="modal-load-mask text-muted d-flex justify-content-center align-items-center py-5 my-5">';
			html += '					<div class="spinner-border text-gray-300"></div>';
			html += '    	 </div>';
			html += '    </div>';
			html += '  </div>';
			html += '</div>';

			$('body').append(html);

			if (typeof add_modal_listner == 'function') add_modal_listner('#ff_modal_fastorder', 'quick-order-' +  + product_id);

			if ($('#ff_modal_qview').is('.show')) {
				$('#ff_modal_qview').modal('hide');
				$('#ff_modal_qview').on('hidden.bs.modal', function (e) {
					$('#ff_modal_fastorder').modal('show');
				});
			} else {
				$('#ff_modal_fastorder').modal('show');
			}

		},
		success: function(data) {
			$('#ff_modal_fastorder .modal-content').html(data);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

var ff_scrlltop = (duration) => {
	step = window.pageYOffset / (duration / 10);
	var id = setInterval(function() {
		if (window.pageYOffset <= 0) {
			clearInterval(id)
		}
		window.scrollBy(0, -step);
	}, 10);
}

var listened_modal_is_open = false;

var add_modal_listner = (id, url = 'dialog') => {

	$(id).on('shown.bs.modal', function (e) {
		history.pushState(null, null, location.href + '#' + url);
		listened_modal_is_open = true;
	});

	$(id).on('hidden.bs.modal', function (e) {
		if ( listened_modal_is_open ) {
			window.history.back();
			listened_modal_is_open = false;
		}
	});

}

var change_color_button_cart = () => {

	var targets = document.querySelectorAll('.product-item');

	targets.forEach(function(target, i) {

		var buttons = target.querySelectorAll('.btn-cart-add')

		target.addEventListener('mouseover', function() {
			buttons.forEach(function(button, i) {
				if (button != null) {
					button.classList.remove('btn-light');
					button.classList.add('btn-danger');
				}
			});
		}, false);

		target.addEventListener('mouseout', function() {
			buttons.forEach(function(button, i) {
				if (button != null) {
					button.classList.remove('btn-danger');
					button.classList.add('btn-light');
				}
			});
		}, false);

	});
}

var lazyImgObserver = new IntersectionObserver((entries, observer) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const lazyImg = entry.target;

      if (lazyImg.hasAttribute('data-src')) lazyImg.setAttribute('src', lazyImg.getAttribute('data-src'));
      if (lazyImg.hasAttribute('data-srcset')) lazyImg.setAttribute('srcset', lazyImg.getAttribute('data-srcset'));

      lazyImg.onload = () => {
        let spinner = lazyImg.parentNode.querySelector('.ft-lazy-spinner');
        if (spinner != null) spinner.remove();
      }

      observer.unobserve(lazyImg);
    }
  })
}, {
    root: null,
    rootMargin: '0px',
    threshold: 0.5
});

var lazyImgObserve = (parent) => {
    const arr = parent.querySelectorAll('.ft-lazy-img')
    arr.forEach(image => {
        lazyImgObserver.observe(image);
    });
}

window.addEventListener('DOMContentLoaded', function(e) {

	document.body.classList.remove('loading');

	if (typeof change_color_button_cart == 'function') change_color_button_cart();
	if (typeof add_modal_listner == 'function') add_modal_listner('#ff_cart', 'cart');
	if (typeof add_modal_listner == 'function') add_modal_listner('#ff_header_contacts', 'contacts');
  if (typeof lazyImgObserve == 'function') lazyImgObserve(document);

});

window.addEventListener('resize', function(e) {
	if (typeof menu_holder_height == 'function')  menu_holder_height();
  if (typeof recombinateMenuDebounce == 'function')  recombinateMenuDebounce();
});

window.addEventListener('scroll', function(e) {

	var scrll_btn = document.querySelector('#scrll-on-top');

	if (scrll_btn != null) {
		if ( window.pageYOffset > 200 ) {
			scrll_btn.classList.remove('d-none');
		} else {
			scrll_btn.classList.add('d-none');
		}
	}
});

window.addEventListener("popstate",function(e){

	if ( listened_modal_is_open ) {
		$('.modal').modal('hide');
		listened_modal_is_open = false;
	}

});
