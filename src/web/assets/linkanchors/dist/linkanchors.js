/**
 * Anchor dropdown for Link fields (see statikbe\entryanchors\services\LinkFields).
 * Lists the anchors of the selected entry and writes the chosen one to the URL suffix input as `#slug`.
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
})(jQuery);
