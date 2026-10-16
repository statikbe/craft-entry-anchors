/**
 * Anchor dropdowns for Link fields (see statikbe\entryanchors\services\LinkFields).
 * - EntryAnchorsLinkField: the anchors of the selected entry, the chosen one goes to the URL suffix input as `#slug`.
 * - EntryAnchorsUrlField: the anchors of the page the field is on, the chosen one becomes the URL (`#slug`) and its title the
 *   label (unless the editor typed one).
 */
(function ($) {
  Craft.EntryAnchorsLinkField = Garnish.Base.extend({
    $entry: null,
    $suffix: null,
    $type: null,
    $wrapper: null,
    $select: null,
    options: [],

    init(id, settings) {
      this.settings = settings;
      this.options = settings.options || [];
      this.$entry = $('#' + id + '-entry');
      this.$suffix = $('#' + id + '-url-suffix');
      this.$type = $('#' + id + '-type');
      this.$select = $('#' + id + '-anchor');
      this.$wrapper = this.$select.closest('.entry-anchors-select');

      const elementSelect = this.$entry.find('.elementselect').data('elementSelect');

      if (!this.$entry.length || !this.$suffix.length || !elementSelect) {
        this.$wrapper.remove();
        return;
      }

      this.$entry.addClass('entry-anchors-container').append(this.$wrapper);
      // Craft centers the link type select vertically, which drops it when the dropdown wraps below the entry select
      this.$entry.parent().addClass('entry-anchors-row');
      this.$wrapper.toggleClass('hidden', !settings.hasEntry);
      this.render();

      elementSelect.on('selectElements', (event) => {
        const element = event.elements && event.elements[0];

        if (element) {
          this.load(element);
        }
      });

      elementSelect.on('removeElements', () => {
        this.options = [];
        this.clearAnchor();
        this.render();
        this.$wrapper.addClass('hidden');
      });

      this.addListener(this.$select, 'change', 'onSelectChange');
      this.addListener(this.$suffix, 'input', 'render');

      // An anchor makes no sense for the other link types
      this.addListener(this.$type, 'change', () => {
        if (this.$type.val() !== 'entry' && this.$select.val()) {
          this.clearAnchor();
          this.render();
        }
      });
    },

    load(element) {
      // A new entry has other anchors
      this.clearAnchor();

      Craft.sendActionRequest('GET', 'entry-anchors/anchors/list', {
        params: { entryId: element.id, siteId: element.siteId },
      })
        .then((response) => {
          this.options = response.data.options || [];
        })
        .catch(() => {
          this.options = [];
          Craft.cp.displayError(this.settings.labels.error);
        })
        .finally(() => {
          this.render();
          this.$wrapper.removeClass('hidden');
        });
    },

    /**
     * Rebuild the dropdown, selecting the anchor in the URL suffix.
     */
    render() {
      const anchor = this.currentAnchor();
      const options = [{ value: '', label: this.settings.labels.none }, ...this.options];

      // Keep a saved anchor visible when its title no longer exists on the page
      if (anchor && !this.options.some((option) => option.value === anchor)) {
        options.push({ value: anchor, label: anchor + ' (' + this.settings.labels.notFound + ')' });
      }

      this.$select.empty();
      options.forEach((option) => {
        this.$select.append($('<option>', { value: option.value, text: option.label }));
      });
      this.$select.val(anchor || '');
    },

    onSelectChange() {
      const anchor = this.$select.val();

      if (anchor) {
        this.setSuffix('#' + anchor);
      } else {
        this.clearAnchor();
      }
    },

    /**
     * The anchor in the URL suffix, if the suffix is one (query params are left alone).
     */
    currentAnchor() {
      const suffix = (this.$suffix.val() || '').trim();

      return suffix.startsWith('#') ? suffix.substring(1) : null;
    },

    clearAnchor() {
      if (this.currentAnchor() !== null) {
        this.setSuffix('');
      }
    },

    setSuffix(value) {
      // Trigger change so the element editor saves a draft
      this.$suffix.val(value).trigger('change');
    },
  });

  Craft.EntryAnchorsUrlField = Garnish.Base.extend({
    $url: null,
    $type: null,
    $linkInput: null,
    $wrapper: null,
    $select: null,
    $label: null,
    options: [],
    autoLabel: null,

    init(id, settings) {
      this.settings = settings;
      this.options = settings.options || [];
      this.$url = $('#' + id + '-url');
      // Craft's "Label" input, when the field shows it (the field's legend has the same id)
      this.$label = $('input[id="' + id + '-label"]');
      this.$type = $('#' + id + '-type');
      this.$linkInput = this.$url.closest('.link-input');
      this.$select = $('#' + id + '-page-anchor');
      this.$wrapper = this.$select.closest('.entry-anchors-select');

      if (!this.$url.length || !this.$linkInput.length || !this.$url.children('input[type=hidden]').length) {
        this.$wrapper.remove();
        return;
      }

      // Below the link row: the URL container itself is drawn as the input box
      this.$wrapper.addClass('entry-anchors-below').insertAfter(this.$linkInput);
      this.render();
      // A label that is the title of the anchor in the URL counts as filled in by the dropdown: it follows another anchor
      const title = this.titleOf(this.currentAnchor());
      if (title && this.$label.val() === title) {
        this.autoLabel = title;
      }

      this.addListener(this.$select, 'change', 'onSelectChange');
      // Typing in the URL input (bubbles up from the text input, which Craft recreates)
      this.addListener(this.$url, 'input', 'render');
      this.addListener(this.$type, 'change', 'updateVisibility');
      this.addListener(Garnish.$win, 'resize', 'updateVisibility');
    },

    /**
     * Rebuild the dropdown, selecting the anchor in the URL. Hidden when the page has no anchors and the URL isn't one.
     */
    render() {
      const anchor = this.currentAnchor();
      const options = [{ value: '', label: this.settings.labels.none }, ...this.options];

      if (anchor && !this.options.some((option) => option.value === anchor)) {
        options.push({ value: anchor, label: anchor + ' (' + this.settings.labels.notFound + ')' });
      }

      this.$select.empty();
      options.forEach((option) => {
        this.$select.append($('<option>', { value: option.value, text: option.label }));
      });
      this.$select.val(anchor || '');
      this.updateVisibility();
    },

    /**
     * Only for the URL link type, and only when the page has anchors or the URL is one. Lined up with the URL input.
     */
    updateVisibility() {
      const isUrl = !this.$type.length || this.$type.val() === 'url';
      this.$wrapper.toggleClass('hidden', !isUrl || this.$select.children().length === 1);

      if (!this.$wrapper.hasClass('hidden')) {
        this.$wrapper.css('margin-inline-start', Math.max(0, this.$url.offset().left - this.$linkInput.offset().left));
      }
    },

    onSelectChange() {
      const anchor = this.$select.val();

      if (anchor) {
        this.setUrl('#' + anchor);
      } else if (this.currentAnchor() !== null) {
        this.setUrl('');
      }
      this.updateLabel(this.titleOf(anchor));
    },

    /**
     * The label becomes the title of the chosen anchor (as in the dropdown) instead of the URL `#anchor`, unless the editor
     * typed a label of their own.
     */
    updateLabel(title) {
      if (!this.$label.length) {
        return;
      }

      const label = this.$label.val();
      if (label && label !== this.autoLabel) {
        return;
      }

      this.autoLabel = title || null;
      this.$label.val(title || '').trigger('input').trigger('change');
    },

    titleOf(anchor) {
      return (anchor && this.options.find((option) => option.value === anchor)?.label) || null;
    },

    /**
     * The anchor in the URL, if the URL is one.
     */
    currentAnchor() {
      const url = (this.$url.children('input[type=hidden]').val() || '').trim();

      return url.startsWith('#') ? url.substring(1) : null;
    },

    setUrl(value) {
      const linkInput = this.$url.data('linkInput');
      const $hidden = this.$url.children('input[type=hidden]');

      // Through Craft's URL input, so its chip/text input and the link label follow
      if (linkInput && typeof linkInput.createTextInput === 'function') {
        linkInput.createTextInput(value);
        if (value && typeof linkInput.maybeSwitchToChip === 'function') {
          linkInput.maybeSwitchToChip();
        }
      } else {
        $hidden.val(value);
      }

      // Trigger change so the element editor saves a draft
      $hidden.trigger('change');
    },
  });
})(jQuery);
