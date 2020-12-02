var ftLiveSearch = {
  'template': {
    'product': (product) => {

      let template = document.getElementById('ftLivesearchResults').content.cloneNode(true);
          item = template.querySelector('.product');

      item.setAttribute('href', product.href);
      item.querySelector('.image').setAttribute('src', product.thumb);
      item.querySelector('.image').setAttribute('srcset', `${product.thumb} 1x, ${product.thumb2x} 2x, ${product.thumb3x} 3x, ${product.thumb4x} 4x`);
      item.querySelector('.name').innerHTML = product.name;

      if (item.querySelector('.description') != null) {
        item.querySelector('.description').innerHTML = product.description;
      }

      if (product.special) {
        item.querySelector('.special').innerHTML = product.price;
        item.querySelector('.price').innerHTML = product.special;
      } else {
        item.querySelector('.special').remove();
        item.querySelector('.price').innerHTML = product.price;
      }

      return item;
    },
    'message': (key) => {

      let template = document.getElementById('ftLivesearchResults').content.cloneNode(true);
          item = template.querySelector('.message'),
          message = '';

      if (key == 'error') message = item.querySelector('.error');
      if (key == 'not_found') message = item.querySelector('.not-found');

      item.innerHTML = '';
      item.append(message);

      return item;
    },
    'linkAll': (link, count) => {

      let template = document.getElementById('ftLivesearchResults').content.cloneNode(true);
          item = template.querySelector('.link-all');

      item.setAttribute('href', link);
      item.querySelector('.count').innerHTML = count;

      return item;
    },
    'spinner': () => {

      let template = document.getElementById('ftLivesearchResults').content.cloneNode(true);
          item = template.querySelector('.spinner');

      return item;
    }
  },
  'getResponse': async(url, dataType = 'json', options = {}) => {
    const response = await fetch(url, options);
    if (response.ok) {
      if (dataType == 'json') {
        return await response.json();
      } else {
        console.log('Data type "' + dataType + '" is not supported');
      }
    } else {
      console.log('Response error no: ' + response.status);
    }
  },
  'results': (e, sub_category = false, description = false, characters = 3) => {

    var category_form = e.target.parentNode.querySelector('[name=\'category_id\']'),
        category_id = category_form != null ? parseInt(category_form.value) : 0,
        request = e.target.value,
        results_container = document.querySelector('#ftSearch .livesearch');


    if (results_container != null) {

      if (request.length < parseInt(characters) || e.target !== document.activeElement) {

        document.body.classList.remove('live-search-open');

      } else {

        document.body.classList.add('live-search-open');

        results_container.innerHTML = '';
        results_container.append(ftLiveSearch.template.spinner());

        var url = 'index.php?route=extension/module/framefreetheme/ff_search/livesearch&search=' + encodeURIComponent(request),
            link = 'index.php?route=product/search&search=' + encodeURIComponent(request);

        if (category_id) {
          url += '&category_id=' + encodeURIComponent(category_id);
          link +='&category_id=' + encodeURIComponent(category_id);
        }

        if (sub_category) {
          url += '&sub_category=true';
          link +='&sub_category=true';
        }

        if (description) {
          url += '&description=true';
          link +='&description=true';
        }

        ftLiveSearch.getResponse(url, 'json').then((json) => {

          results_container.innerHTML = '';

          if (json.products.length > 0) {

            json.products.forEach(function(product, i) {

              results_container.append(ftLiveSearch.template.product(product));

            });

            results_container.append(ftLiveSearch.template.linkAll(link, json.total));

          } else {

            results_container.append(ftLiveSearch.template.message('not_found'));

          }

          return false;

        }).catch(function(e) {

          results_container.innerHTML = '';
          results_container.append(ftLiveSearch.template.message('error'));
          console.log(e)

        });

      }
    }
  },
}

var ftLiveSearchDebounce = debounce(ftLiveSearch.results, 500);
