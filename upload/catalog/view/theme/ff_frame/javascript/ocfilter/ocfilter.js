// https://codepen.io/martinAnsty/pen/BCotE
Math.easeIn = function (val, min, max, strength) {
	val /= max;

	return (max - 1) * Math.pow(val, strength) + min;
};

(function($) {
  function setSlider(_, target) {
    var
      that = this,
      $element = $(target),
      min = parseFloat($element.data().rangeMin),
      max = parseFloat($element.data().rangeMax),
      decimals = 0,
      elementMin,
      elementMax,
      controlMin,
      controlMax,
      _options = {
        behaviour: 'drag-tap',
        connect: true,
        range: {
          'min': min,
          'max': max
        }
      };

    // Logarithmic scale
    if ($element.data().optionId == 'p' && (max - min) > 100) {
      _options['pips'] = {
        mode: 'range',
    		density: 4
    	};

      var _i = 25, _strength = 3.5;

      if ((max - min) < 100) {
        _strength = 2;
      }

      for (; _i < 100; _i += 25) {
        _options['range'][_i + '%'] = Math.ceil(Math.easeIn(((max - min) / 100 * _i), min, max, _strength));
      }
    } else {
      _options['pips'] = {
        mode: 'count',
        values: 3,
    		density: 4
    	};
    }

    if (max && min != max) {
      _options['start'] = [ parseFloat($element.data().startMin), parseFloat($element.data().startMax) ];
    } else {
      _options['start'] = parseFloat($element.data().startMin);
    }

    // Decimal
    if (/\./.test($element.data().rangeMin) || /\./.test($element.data().rangeMax)) {
      decimals = Math.max(
        $element.data().rangeMin.toString().replace(/^\d+?\./, '').length,
        $element.data().rangeMax.toString().replace(/^\d+?\./, '').length
      );
    }

    _options['format'] = {
  	  to: function (value) {
  		  return parseFloat(value).toFixed(decimals);
  	  },
  	  from: function (value) {
  		  return parseFloat(value).toFixed(decimals);
  	  }
  	};

  	noUiSlider.create($element.get(0), _options);

    if ($element.data().elementMin && $($element.data().elementMin).length) {
      elementMin = $($element.data().elementMin);
    }

    if ($element.data().elementMax && $($element.data().elementMax).length) {
      elementMax = $($element.data().elementMax);
    }

    $element.get(0).noUiSlider.on('slide', function(values, handle, noformat) {
      if (typeof values[0] != 'undefined') {
        if (elementMin) {
          elementMin.html(values[0]);
        }

        if ($element.data().controlMin && $($element.data().controlMin).length) {
          $($element.data().controlMin).val(noformat[0].toFixed(decimals));
        }
      }

      if (typeof values[1] != 'undefined') {
        if (elementMax) {
          elementMax.html(values[1]);
        }

        if ($element.data().controlMax && $($element.data().controlMax).length) {
          $($element.data().controlMax).val(noformat[1].toFixed(decimals));
        }
      }
    });

    $element.get(0).noUiSlider.on('change', function(_, __, values, tap, positions) {
      that.params.remove.call(that, $element.data().optionId);

      if ((positions[1] - positions[0]) < 100) {
        that.params.set.call(that, $element.data().optionId, values[0].toFixed(decimals) + '-' + values[1].toFixed(decimals));
      }

      that.update($element);
    });

    if ($element.data().controlMin) {
      that.$element.on('change', $element.data().controlMin, function(e) {
        if (this.value == '') {
          return false;
        }

        if (this.value < min || this.value > max) {
          this.value = min;
        }

        $element.get(0).noUiSlider.set([this.value, null]);
      });
    }

    if ($element.data().controlMax) {
      that.$element.on('change', $element.data().controlMax, function(e) {
        if (this.value == '') {
          return false;
        }

        if (this.value < min || this.value > max) {
          this.value = max;
        }

        $element.get(0).noUiSlider.set([null, this.value]);
      });
    }
  };

  var ocfilter = {
    timers: {},
    values: {},
    options: {},
    init: function(options) {
      this.options = $.extend({}, options);

      this.$element = $('#ocfilter');

      this.$fields = $('.option-values input', this.$element);

      this.$target = $('.ocf-target', this.$element);
      this.$values = $('label', this.$element);

      var that = this;

      this.$values.each(function() {
        that.values[$(this).attr('id')] = this;
      });

      this.$target.on('change', function(e) {
        e.preventDefault();

        var
          $element = $(this),
          $buttonTarget = $element.closest('label'),
          $dropdown = $element.closest('.dropdown');

        that.options.php.params = $element.val();

        if ($element.is(':radio')) {
          $element.closest('.ocf-option-values').find('label.ocf-selected').removeClass('ocf-selected');
        }

        $buttonTarget.toggleClass('ocf-selected', $element.prop('checked'));

        that.update($buttonTarget);
      });

      this.$element.on('click.ocf', '.dropdown-menu', function(e) {
        $(this).closest('.dropdown').one('hide.bs.dropdown', function(e) {
          return false;
        });
      });

      this.$element.on('click.ocf', '.disabled, [disabled]', function(e) {
        e.stopPropagation();
        e.preventDefault();
      });

      var hovered = false;

      this.$element.on({
        'mouseenter': function(e) {
          hovered = true;
        },
        'mouseleave': function(e) {
          hovered = false;

          $('[aria-describedby="' + $(this).attr('id') + '"]').popover('toggle');
        }
      }, '.popover').on('hide.bs.popover', '[aria-describedby^="popover"]', function(e) {
        setTimeout(function(element) {
          $(element).show();
        }, 0, e.target);

        if (hovered) {
          e.preventDefault();
        }
      });

      this.$element.find('.dropdown').on('hide.bs.dropdown', function(e) {
        that.$element.find('[aria-describedby^="popover"]').popover('hide');
      });

      if (this.options.php.manualPrice) {
        $('[data-toggle=\'popover-price\']').popover({
          content: function() {
            return '' +
              '<div class="form-inline">' +
                '<div class="form-group">' +
                  '<input name="price[min]" value="' + $('#price-from').text() + '" type="text" class="form-control input-sm" id="min-price-value" />' +
                '</div>' +
                '<div class="form-group">&nbsp;-&nbsp;</div>' +
                '<div class="form-group">' +
                  '<input name="price[max]" value="' + $('#price-to').text() + '" type="text" class="form-control input-sm" id="max-price-value" />' +
                '</div>' +
              '</div>';
          },
          html: true,
					sanitize: false,
          delay: { 'show': 700, 'hide': 500 },
          placement: 'top',
          container: '#ocfilter',
          title: 'Указать цену',
          trigger: 'hover'
        });
      }

      // Set sliders
      $('#ocfilter .scale').each($.proxy(setSlider, this));
    },

    update: function(scrollTarget) {
      var
        that = this,
        isSlider = scrollTarget.hasClass('scale'),
        data = {
          path: this.options.php.path,
          option_id: scrollTarget.data().optionId
        };

      if (this.options.php.params) {
        data[this.options.php.index] = this.options.php.params;
      }

      this.preload();

      $.get('index.php?route=extension/module/ocfilter/callback', data, function(json) {
        /* Start update */
        for (var i in json.values) {
          var value = json.values[i],
            target = $(that.values['v-' + i]),
            total = value.t,
            selected = value.s,
            params = value.p;

          if (target.length > 0) {
            if (target.is('label')) {
              if (total === 0 && !selected) {
                target.addClass('disabled').removeClass('ocf-selected').find('input').attr('disabled', true).prop('checked', false);
              } else {
                target.removeClass('disabled').find('input').removeAttr('disabled');
              }

              $('input', target).val(params);

              if (that.options.php.showCounter) {
                $('small', target).text(total);
              }
            } else {
              target.prop('disabled', (total === 0)).val(params);
            }
          }
        }

        if (json.total === 0) {
          $('#ocfilter-button button').removeAttr('onclick').addClass('disabled').text(that.options.text.select);

          if (typeof scrollTarget != 'undefined' && scrollTarget.hasClass('scale')) {
            $('#ocfilter .scale').removeAttr('disabled');
          }
        } else {
          if (that.options.php.searchButton || isSlider) {
            $('#ocfilter-button button').attr('onclick', 'location = \'' + json.href + '\'').removeClass('disabled').text(json.text_total);

            //$('#ocfilter .scale').removeAttr('disabled');
          } else {
            window.location = json.href;

            return;
          }
        }

        that.$fields.filter('.enabled').removeAttr('disabled');

        if (typeof scrollTarget != 'undefined') {
          that.scroll(scrollTarget);
        }

        if (isSlider) {
          scrollTarget.removeAttr('disabled');
        }

        if (!$.isPlainObject(json.sliders) || $.isEmptyObject(json.sliders)) {
          return;
        }

        for (var option_id in json.sliders) {
          if ($('.scale[data-option-id="' + option_id + '"]').length < 1) {
            continue;
          }

          var
            $element = $('.scale[data-option-id="' + option_id + '"]').removeAttr('disabled'),
            slider = $element.get(0).noUiSlider,
            hasParam = that.params.has.call(that, option_id),
            min = parseFloat(json.sliders[option_id]['min']),
            max = parseFloat(json.sliders[option_id]['max']),
            min_value = min,
            max_value = max,
            set = slider.get();

          if (!$.isArray(set)) {
            set = [set, set];
          }

          if (hasParam) {
            if (set[1] <= max) {
              max_value = set[1];
            }

            if (set[0] >= min) {
              min_value = set[0];
            }
          }

          if (min != max) {
            slider.destroy();

            $element.data({
              startMin: min_value,
              startMax: max_value,
              rangeMin: min,
              rangeMax: max
            });

            if ($element.data().controlMin && $($element.data().controlMin).length) {
              $($element.data().controlMin).val(min_value);
            }

            if ($element.data().controlMax && $($element.data().controlMax).length) {
              $($element.data().controlMax).val(max_value);
            }

            if ($element.data().elementMin && $($element.data().elementMin).length) {
              $($element.data().elementMin).html(min_value);
            }

            if ($element.data().elementMax && $($element.data().elementMax).length) {
              $($element.data().elementMax).html(max_value);
            }

            setSlider.call(that, 0, $element.get(0));
          } else {
            $element.attr('disabled', 'disabled');
          }
        }
        /* End update */
      }, 'json');
    },

    params: {
      decode: function() {
        var params = {};
        if (this.options.php.params) {
          var matches = this.options.php.params.split(';');
          for (var i = 0; i < matches.length; i++) {
            var parts = matches[i].split(':');
            params[parts[0]] = typeof parts[1] != 'undefined' ? parts[1].split(',') : [];
          }
        }
        this.options.php.params = params;
      },

      encode: function() {
        var params = [];
        if (this.options.php.params) {
          for (i in this.options.php.params) {
            params.push(i + ':' + (typeof this.options.php.params[i] == 'object' ? this.options.php.params[i].join(',') : this.options.php.params[i]));
          }
        }
        this.options.php.params = params.join(';');
      },

      set: function(option_id, value_id) {
        this.params.decode.call(this);
        if (typeof this.options.php.params[option_id] != 'undefined') {
          this.options.php.params[option_id].push(value_id);
        } else {
          this.options.php.params[option_id] = [value_id];
        }
        this.params.encode.call(this);
      },

      has: function(option_id) {
        this.params.decode.call(this);

        var has = (typeof this.options.php.params[option_id] != 'undefined');

        this.params.encode.call(this);

        return has;
      },

      remove: function(option_id, value_id) {
        this.params.decode.call(this);
        if (typeof this.options.php.params[option_id] != 'undefined') {
          if (this.options.php.params[option_id].length === 1 || !value_id) {
            delete this.options.php.params[option_id];
          } else {
            this.options.php.params[option_id].splice(ocfilter.options.php.params[option_id].indexOf(value_id), 1);
          }
        }
        this.params.encode.call(this);
      }
    },

    preload: function() {
      if ($('.ocfilter-option-popover').length) {
        $('.ocfilter-option-popover button').button('loading');
      }

      this.$element.find('.scale').attr('disabled', 'disabled');
      this.$values.addClass('disabled').find('small').text('0');
    },

    scroll: function(target) {
      var that = this;

      if (target.find('input:checked').length < 1 && target.parent().find('input:checked').length > 0) {
        target = target.parent().find('input:checked:first').parent();
      }

      if (that.options.mobile && target.is('label')) {
        target = target.find('input');
      }

      if (target.is(':hidden')) {
        target = target.parents(':visible:first');
      }

      this.$element
        .find('[aria-describedby^="popover"]')
        .not('[data-toggle="popover-price"]')
        .not(target)
				//.popover('destroy');
        .popover('dispose');

      if (!target.attr('aria-describedby')) {
        var options = {
          placement: that.options.mobile ? 'bottom' : 'right',
          selector: that.options.mobile ? '> input' : false,
          delay: { 'show': 400, 'hide': 600 },
          content: function() {
            return $('#ocfilter-button').html();
          },
          container: that.$element,
          trigger: 'hover',
          html: true,
					sanitize: false
        };

        target.popover(options).popover('show');

        $('#' + target.attr('aria-describedby')).addClass('ocfilter-option-popover');
      } else {
        $('#' + target.attr('aria-describedby') + ' button').replaceWith($('#ocfilter-button').html());
      }
    }
  };

  /* IE6+ */
  if (Object.create === undefined) {
    Object.create = function(object) {
      function f() {};
      f.prototype = object;
      return new f();
    };
  }

  $.fn.ocfilter = function(options) {
    return this.each(function() {
      var $element = $(this);

      if ($element.data('ocfilter')) {
        return $element.data('ocfilter');
      }

      $element.data('ocfilter', Object.create(ocfilter).init(options, $element));
    });
  };
})(jQuery);
