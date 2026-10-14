/**
 * Anchor Link fields (see statikbe\entryanchors\fields\AnchorLink):
 * - the anchor follows the title while the block isn't published yet (settings.follow), and is kept after that;
 * - "Edit" shows the anchor input; editing it stops the anchor from following the title;
 * - the copy button copies the link to the block, `#anchor` or the page URL + `#anchor` (the copyFormat setting).
 * Slugs come from the server (the same slugs the front end uses), fetched while typing, so the copy happens right in the click.
 */
(function ($) {
  Craft.EntryAnchorsAnchorLink = Garnish.Base.extend({
    $title: null,
    $anchor: null,
    $anchorLine: null,
    $anchorLabel: null,
    $anchorField: null,
    $copy: null,
    follow: false,
    anchor: null,
    request: 0,
    timeout: null,

    init(id, settings) {
      this.settings = settings;
      this.follow = !!settings.follow;
      this.$title = $('#' + id);
      this.$anchor = $('#' + id + '-anchor');
      this.$anchorLine = $('#' + id + '-anchor-line');
      this.$anchorLabel = $('#' + id + '-anchor-label');
      this.$anchorField = $('#' + id + '-anchor-field');
      this.$copy = $('#' + id + '-copy');

      this.anchor = this.$anchor.val() || null;
      this.$copy.prop('disabled', !this.anchor);

      this.addListener(this.$title, 'input', () => {
        if (this.follow || !this.$anchor.val()) {
          this.debounce(() => this.slugify(this.$title.val(), true));
        }
        this.updateVisibility();
      });

      this.addListener(this.$anchor, 'input', () => {
        // A hand-made anchor is kept
        this.follow = false;
        this.debounce(() => this.slugify(this.$anchor.val() || this.$title.val(), false));
      });

      this.addListener($('#' + id + '-anchor-edit'), 'click', () => {
        this.$anchorField.removeClass('hidden');
        this.$anchorLine.addClass('hidden');
        this.$anchor.trigger('focus');
      });

      this.addListener(this.$copy, 'click', 'copy');
      this.updateVisibility();
    },

    debounce(callback) {
      clearTimeout(this.timeout);
      this.$copy.prop('disabled', true);
      this.timeout = setTimeout(callback, 300);
    },

    /**
     * The anchor for a text; with setInput, it also goes into the anchor input (when following the title).
     */
    slugify(text, setInput) {
      text = (text || '').trim();
      const request = ++this.request;

      if (!text) {
        this.setAnchor(null, setInput);
        return;
      }

      Craft.sendActionRequest('GET', 'entry-anchors/anchors/slug', { params: { title: text } }).then((response) => {
        // Only the answer to the last request counts
        if (request === this.request) {
          this.setAnchor(response.data.slug || null, setInput);
        }
      });
    },

    setAnchor(anchor, setInput) {
      this.anchor = anchor;
      this.$anchorLabel.text('#' + (anchor || ''));
      this.$copy.prop('disabled', !anchor);

      if (setInput) {
        // Trigger change so the element editor saves a draft
        this.$anchor.val(anchor || '').trigger('change');
      }
    },

    updateVisibility() {
      const hasTitle = !!this.$title.val().trim();
      // The anchor line, unless the anchor input is open
      this.$anchorLine.toggleClass('hidden', !hasTitle || !this.$anchorField.hasClass('hidden'));
    },

    copy() {
      if (!this.anchor) {
        return;
      }

      const anchor = '#' + this.anchor;
      const text = this.settings.copyFormat === 'anchor' || !this.settings.url ? anchor : this.settings.url + anchor;
      const copied = () => Craft.cp.displayNotice(this.settings.copied);

      if (!navigator.clipboard) {
        this.copyFallback(text) ? copied() : Craft.cp.displayError(this.settings.failed);
        return;
      }

      navigator.clipboard.writeText(text).then(copied, () => {
        this.copyFallback(text) ? copied() : Craft.cp.displayError(this.settings.failed);
      });
    },

    /**
     * When the Clipboard API isn't there or isn't allowed (http, some browsers' permissions).
     */
    copyFallback(text) {
      const $textarea = $('<textarea>', { readonly: true, css: { position: 'fixed', top: 0, left: 0, opacity: 0 } })
        .val(text)
        .appendTo(document.body);
      $textarea[0].select();
      let copied = false;
      try {
        copied = document.execCommand('copy');
      } catch (e) {
        copied = false;
      }
      $textarea.remove();
      this.$copy.trigger('focus');

      return copied;
    },
  });
})(jQuery);
